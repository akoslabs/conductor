<?php

declare(strict_types=1);

namespace Conductor\Agents;

use Conductor\Contracts\AgentBuilderInterface;
use Conductor\Contracts\AgentResponseInterface;
use Conductor\Contracts\MemoryStoreInterface;
use Conductor\Contracts\RetrieverInterface;
use Conductor\Contracts\ToolInterface;
use Conductor\Enums\MessageRole;
use Conductor\Events\AgentCompleted;
use Conductor\Events\AgentFailed;
use Conductor\Events\AgentStarted;
use Conductor\Exceptions\AgentException;
use Conductor\Exceptions\TokenBudgetExceededException;
use Conductor\Memory\MessageMapper;
use Conductor\Monitoring\BudgetEnforcer;
use Conductor\Monitoring\UsageTracker;
use Conductor\Tools\ToolAdapter;
use Generator;
use Prism\Prism\Contracts\Message;
use Prism\Prism\Prism;
use Prism\Prism\Text\Chunk;
use Prism\Prism\Text\Response;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

final class AgentBuilder implements AgentBuilderInterface
{
    private ?string $provider = null;

    private ?string $model = null;

    private ?string $systemPrompt = null;

    /** @var array<int, class-string|ToolInterface> */
    private array $tools = [];

    private ?string $conversationId = null;

    private bool $memoryEnabled = false;

    /** @var array<int, array{provider: string, model: string}> */
    private array $fallbacks = [];

    private ?int $tokenBudget = null;

    /** @var array<string, mixed>|null */
    private ?array $schema = null;

    private int $maxSteps = 1;

    /** @var array<string, mixed> */
    private array $metadata = [];

    private ?RetrieverInterface $retriever = null;

    private int $ragLimit = 5;

    /**
     * @param  string  $name  The agent name identifier.
     */
    public function __construct(
        private readonly string $name,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function using(string $provider, string $model): static
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withSystemPrompt(string $prompt): static
    {
        $this->systemPrompt = $prompt;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withTools(array $tools): static
    {
        $this->tools = $tools;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMemory(?string $conversationId = null): static
    {
        $this->memoryEnabled = true;
        $this->conversationId = $conversationId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withFallback(string $provider, string $model): static
    {
        $this->fallbacks[] = ['provider' => $provider, 'model' => $model];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withTokenBudget(int $maxTokens): static
    {
        $this->tokenBudget = $maxTokens;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withSchema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMaxSteps(int $steps): static
    {
        $this->maxSteps = $steps;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Attach a retriever for RAG-augmented prompts.
     *
     * @param  RetrieverInterface  $retriever  The retriever to use.
     * @param  int  $limit  Maximum number of chunks to retrieve.
     */
    public function withRag(RetrieverInterface $retriever, int $limit = 5): static
    {
        $this->retriever = $retriever;
        $this->ragLimit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $input): AgentResponseInterface
    {
        $provider = $this->resolveProvider();
        $model = $this->resolveModel();

        BudgetEnforcer::checkPerHourBudget($this->name);

        event(new AgentStarted($this->name, $input, $this->metadata));

        $startTime = hrtime(true);

        $providers = array_merge(
            [['provider' => $provider, 'model' => $model]],
            $this->fallbacks,
            $this->resolveConfigFallbacks(),
        );

        $lastException = null;

        foreach ($providers as $providerConfig) {
            try {
                $response = $this->executePrismCall(
                    $providerConfig['provider'],
                    $providerConfig['model'],
                    $input,
                );

                $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

                $agentResponse = new AgentResponse(
                    prismResponse: $response,
                    provider: $providerConfig['provider'],
                    model: $providerConfig['model'],
                    durationMs: $durationMs,
                    metadata: $this->metadata,
                );

                $budgetLimit = $this->tokenBudget ?? config('conductor.budgets.per_request');
                if ($budgetLimit !== null) {
                    $totalTokens = $agentResponse->promptTokens() + $agentResponse->completionTokens();
                    if ($totalTokens > $budgetLimit) {
                        throw new TokenBudgetExceededException($this->name, $totalTokens, $budgetLimit);
                    }
                }

                $this->storeMemoryAfterRun($input, $agentResponse);
                $this->trackUsage($agentResponse, $durationMs);

                event(new AgentCompleted($this->name, $agentResponse, $durationMs));

                return $agentResponse;
            } catch (TokenBudgetExceededException $e) {
                throw $e;
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

        event(new AgentFailed($this->name, $lastException, $input));

        throw new AgentException(
            agentName: $this->name,
            message: $lastException->getMessage(),
            previous: $lastException,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function stream(string $input): Generator
    {
        $provider = $this->resolveProvider();
        $model = $this->resolveModel();

        event(new AgentStarted($this->name, $input, $this->metadata));

        $startTime = hrtime(true);

        try {
            $prismTools = $this->resolvePrismTools();

            $pendingRequest = Prism::text()
                ->using($provider, $model)
                ->withMaxSteps($this->maxSteps);

            $systemPrompt = $this->buildSystemPrompt($input);
            if ($systemPrompt !== null) {
                $pendingRequest->withSystemPrompt($systemPrompt);
            }

            if (count($prismTools) > 0) {
                $pendingRequest->withTools($prismTools);
            }

            $messages = $this->resolveMessages();
            if (count($messages) > 0) {
                $messages[] = new UserMessage($input);
                $pendingRequest->withMessages($messages);
            } else {
                $pendingRequest->withPrompt($input);
            }

            $chunks = $pendingRequest->asStream();

            /** @var Chunk $chunk */
            foreach ($chunks as $chunk) {
                yield $chunk;
            }
        } catch (Throwable $e) {
            event(new AgentFailed($this->name, $e, $input));

            throw new AgentException(
                agentName: $this->name,
                message: $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Execute the Prism call with the given provider and model.
     *
     * @param  string  $provider  The provider name.
     * @param  string  $model  The model identifier.
     * @param  string  $input  The user input.
     */
    private function executePrismCall(string $provider, string $model, string $input): Response
    {
        $prismTools = $this->resolvePrismTools();

        $pendingRequest = Prism::text()
            ->using($provider, $model)
            ->withMaxSteps($this->maxSteps);

        $systemPrompt = $this->buildSystemPrompt($input);
        if ($systemPrompt !== null) {
            $pendingRequest->withSystemPrompt($systemPrompt);
        }

        if (count($prismTools) > 0) {
            $pendingRequest->withTools($prismTools);
        }

        $messages = $this->resolveMessages();
        if (count($messages) > 0) {
            $messages[] = new UserMessage($input);
            $pendingRequest->withMessages($messages);
        } else {
            $pendingRequest->withPrompt($input);
        }

        return $pendingRequest->asText();
    }

    /**
     * Build the system prompt, including RAG context if configured.
     *
     * @param  string  $input  The user input for RAG retrieval.
     */
    private function buildSystemPrompt(string $input): ?string
    {
        $prompt = $this->systemPrompt;

        if ($this->retriever !== null) {
            $chunks = $this->retriever->retrieve($input, $this->ragLimit);
            if (count($chunks) > 0) {
                $context = implode("\n\n---\n\n", array_map(
                    fn (array $chunk): string => $chunk['content'],
                    $chunks,
                ));

                $ragPrefix = "Use the following context to help answer the user's question:\n\n{$context}\n\n---\n\n";
                $prompt = $prompt !== null ? $ragPrefix.$prompt : $ragPrefix.'You are a helpful assistant.';
            }
        }

        return $prompt;
    }

    /**
     * Resolve tool instances and convert to Prism tools.
     *
     * @return array<int, Tool>
     */
    private function resolvePrismTools(): array
    {
        $prismTools = [];

        foreach ($this->tools as $tool) {
            $toolInstance = is_string($tool) ? app($tool) : $tool;
            $prismTools[] = ToolAdapter::toPrismTool($toolInstance, $this->name);
        }

        return $prismTools;
    }

    /**
     * Resolve conversation history messages for memory.
     *
     * @return array<int, Message>
     */
    private function resolveMessages(): array
    {
        if (! $this->memoryEnabled || $this->conversationId === null) {
            return [];
        }

        if (! app()->bound(MemoryStoreInterface::class)) {
            return [];
        }

        $memoryStore = app(MemoryStoreInterface::class);
        $maxMessages = config('conductor.memory.max_messages', 50);
        $history = $memoryStore->retrieve($this->conversationId, $this->name, $maxMessages);

        return MessageMapper::toPrismMessages($history);
    }

    /**
     * Store the user input and assistant response in memory.
     *
     * @param  string  $input  The user input.
     * @param  AgentResponseInterface  $response  The agent response.
     */
    private function storeMemoryAfterRun(string $input, AgentResponseInterface $response): void
    {
        if (! $this->memoryEnabled || $this->conversationId === null) {
            return;
        }

        if (! app()->bound(MemoryStoreInterface::class)) {
            return;
        }

        $memoryStore = app(MemoryStoreInterface::class);

        $memoryStore->store($this->conversationId, $this->name, MessageRole::User, $input);
        $memoryStore->store($this->conversationId, $this->name, MessageRole::Assistant, $response->text());
    }

    /**
     * Track usage if enabled.
     *
     * @param  AgentResponseInterface  $response  The agent response.
     * @param  int  $durationMs  Execution duration in milliseconds.
     */
    private function trackUsage(AgentResponseInterface $response, int $durationMs): void
    {
        if (! config('conductor.usage.enabled', true)) {
            return;
        }

        if (! app()->bound(UsageTracker::class)) {
            return;
        }

        app(UsageTracker::class)->track(
            agentName: $this->name,
            provider: $response->provider(),
            model: $response->model(),
            promptTokens: $response->promptTokens(),
            completionTokens: $response->completionTokens(),
            costUsd: $response->costUsd(),
            durationMs: $durationMs,
        );
    }

    /**
     * Resolve the provider to use.
     */
    private function resolveProvider(): string
    {
        return $this->provider ?? config('conductor.default_provider', 'anthropic');
    }

    /**
     * Resolve the model to use.
     */
    private function resolveModel(): string
    {
        return $this->model ?? config('conductor.default_model', 'claude-sonnet-4-20250514');
    }

    /**
     * Resolve fallback providers from config.
     *
     * @return array<int, array{provider: string, model: string}>
     */
    private function resolveConfigFallbacks(): array
    {
        return config('conductor.fallbacks', []);
    }
}
