<?php

declare(strict_types=1);

namespace Conductor\Tools;

use Conductor\Contracts\ToolInterface;
use Conductor\Events\ToolExecuted;
use Prism\Prism\Tool as PrismTool;

final class ToolAdapter
{
    /**
     * Convert a Conductor ToolInterface to a Prism Tool.
     *
     * @param  ToolInterface  $tool  The Conductor tool to adapt.
     * @param  string  $agentName  The agent name for event dispatching.
     */
    public static function toPrismTool(ToolInterface $tool, string $agentName): PrismTool
    {
        $prismTool = (new PrismTool)
            ->as($tool->name())
            ->for($tool->description());

        $parameters = $tool->parameters();
        $properties = $parameters['properties'] ?? [];
        $required = $parameters['required'] ?? [];

        foreach ($properties as $paramName => $paramSchema) {
            $schema = SchemaMapper::toSchema($paramName, $paramSchema);
            $isRequired = in_array($paramName, $required, true);
            $prismTool->withParameter($schema, $isRequired);
        }

        $prismTool->using(function (mixed ...$args) use ($tool, $agentName): string {
            $startTime = hrtime(true);
            $result = $tool->execute($args);
            $durationMs = (int) ((hrtime(true) - $startTime) / 1_000_000);

            $stringResult = is_array($result) ? json_encode($result, JSON_THROW_ON_ERROR) : $result;

            event(new ToolExecuted(
                toolName: $tool->name(),
                arguments: $args,
                result: $result,
                durationMs: $durationMs,
                agentName: $agentName,
            ));

            return $stringResult;
        });

        return $prismTool;
    }
}
