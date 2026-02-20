<?php

declare(strict_types=1);

namespace Conductor\Agents\Concerns;

use Conductor\Contracts\ToolInterface;

trait HasTools
{
    /**
     * Resolve tool class-strings to instances from the container.
     *
     * @param  array<int, class-string|ToolInterface>  $tools
     * @return array<int, ToolInterface>
     */
    protected function resolveTools(array $tools): array
    {
        return array_map(
            fn (string|object $tool) => is_string($tool) ? app($tool) : $tool,
            $tools,
        );
    }
}
