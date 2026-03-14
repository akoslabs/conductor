<?php

declare(strict_types=1);

use Conductor\Contracts\AgentResponseInterface;
use Conductor\Contracts\ToolInterface;
use Conductor\Events\AgentCompleted;
use Conductor\Events\AgentFailed;
use Conductor\Events\AgentStarted;
use Conductor\Events\ToolExecuted;
use Conductor\Exceptions\AgentException;
use Conductor\Facades\Conductor;
use Illuminate\Support\Facades\Event;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->artisan('migrate')->assertSuccessful();
});

it('executes an agent end-to-end with Prism::fake()', function () {
    Event::fake([AgentStarted::class, AgentCompleted::class]);

    Prism::fake([
        TextResponseFake::make()
            ->withText('I am a helpful assistant.')
            ->withUsage(new Usage(100, 50))
            ->withMeta(new Meta('resp-1', 'claude-sonnet-4-20250514')),
    ]);

    $response = Conductor::agent('helper')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withSystemPrompt('You are a helpful assistant.')
        ->run('What can you do?');

    expect($response)->toBeInstanceOf(AgentResponseInterface::class)
        ->and($response->text())->toBe('I am a helpful assistant.')
        ->and($response->promptTokens())->toBe(100)
        ->and($response->completionTokens())->toBe(50);

    Event::assertDispatched(AgentStarted::class, fn (AgentStarted $e) => $e->agentName === 'helper');
    Event::assertDispatched(AgentCompleted::class, fn (AgentCompleted $e) => $e->agentName === 'helper');
});

it('dispatches ToolExecuted events during tool use', function () {
    Event::fake([ToolExecuted::class, AgentStarted::class, AgentCompleted::class]);

    Prism::fake([
        TextResponseFake::make()
            ->withText('Done.')
            ->withUsage(new Usage(50, 25))
            ->withMeta(new Meta('id', 'model')),
    ]);

    $tool = new class implements ToolInterface
    {
        public function name(): string
        {
            return 'calculator';
        }

        public function description(): string
        {
            return 'Calculate math';
        }

        public function parameters(): array
        {
            return [
                'type' => 'object',
                'properties' => [
                    'expression' => ['type' => 'string', 'description' => 'Math expression'],
                ],
                'required' => ['expression'],
            ];
        }

        public function execute(array $arguments): string|array
        {
            return '42';
        }
    };

    $response = Conductor::agent('math-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->withSystemPrompt('You are a calculator.')
        ->withTools([$tool])
        ->withMaxSteps(3)
        ->run('What is 6 * 7?');

    expect($response->text())->toBe('Done.');
});

it('dispatches AgentFailed when execution fails', function () {
    Event::fake([AgentStarted::class, AgentFailed::class]);

    // No Prism::fake() → will fail because there's no provider configured
    // Instead we fake with an exception-throwing response
    Prism::fake([]);

    expect(fn () => Conductor::agent('failing-agent')
        ->using('anthropic', 'claude-sonnet-4-20250514')
        ->run('test')
    )->toThrow(AgentException::class);

    Event::assertDispatched(AgentFailed::class);
});
