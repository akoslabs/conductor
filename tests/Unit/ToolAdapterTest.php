<?php

declare(strict_types=1);

use Conductor\Contracts\ToolInterface;
use Conductor\Events\ToolExecuted;
use Conductor\Tools\ToolAdapter;
use Illuminate\Support\Facades\Event;
use Prism\Prism\Tool as PrismTool;

it('converts a ToolInterface to a Prism Tool', function () {
    $tool = new class implements ToolInterface
    {
        public function name(): string
        {
            return 'test-tool';
        }

        public function description(): string
        {
            return 'A test tool';
        }

        public function parameters(): array
        {
            return [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search query'],
                ],
                'required' => ['query'],
            ];
        }

        public function execute(array $arguments): string|array
        {
            return 'result for: '.$arguments['query'];
        }
    };

    $prismTool = ToolAdapter::toPrismTool($tool, 'test-agent');

    expect($prismTool)->toBeInstanceOf(PrismTool::class)
        ->and($prismTool->name())->toBe('test-tool')
        ->and($prismTool->description())->toBe('A test tool')
        ->and($prismTool->hasParameters())->toBeTrue();
});

it('dispatches ToolExecuted event when tool closure runs', function () {
    Event::fake([ToolExecuted::class]);

    $tool = new class implements ToolInterface
    {
        public function name(): string
        {
            return 'echo-tool';
        }

        public function description(): string
        {
            return 'Echoes input';
        }

        public function parameters(): array
        {
            return [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string', 'description' => 'Message'],
                ],
                'required' => ['message'],
            ];
        }

        public function execute(array $arguments): string|array
        {
            return 'echo: '.$arguments['message'];
        }
    };

    $prismTool = ToolAdapter::toPrismTool($tool, 'test-agent');
    $result = $prismTool->handle(message: 'hello');

    expect($result)->toBe('echo: hello');

    Event::assertDispatched(ToolExecuted::class, function (ToolExecuted $event) {
        return $event->toolName === 'echo-tool'
            && $event->agentName === 'test-agent'
            && $event->result === 'echo: hello';
    });
});

it('converts array results to JSON string', function () {
    Event::fake([ToolExecuted::class]);

    $tool = new class implements ToolInterface
    {
        public function name(): string
        {
            return 'array-tool';
        }

        public function description(): string
        {
            return 'Returns array';
        }

        public function parameters(): array
        {
            return ['type' => 'object', 'properties' => [], 'required' => []];
        }

        public function execute(array $arguments): string|array
        {
            return ['key' => 'value'];
        }
    };

    $prismTool = ToolAdapter::toPrismTool($tool, 'agent');
    $result = $prismTool->handle();

    expect($result)->toBe('{"key":"value"}');
});
