<?php

declare(strict_types=1);

namespace Conductor\Monitoring;

final class CostCalculator
{
    /**
     * Pricing per million tokens [input, output] in USD.
     *
     * @var array<string, array{input: float, output: float}>
     */
    private const PRICING = [
        // Anthropic
        'claude-sonnet-4-20250514' => ['input' => 3.0, 'output' => 15.0],
        'claude-opus-4-20250514' => ['input' => 15.0, 'output' => 75.0],
        'claude-haiku-3-5-20241022' => ['input' => 0.80, 'output' => 4.0],

        // OpenAI
        'gpt-4o' => ['input' => 2.50, 'output' => 10.0],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4-turbo' => ['input' => 10.0, 'output' => 30.0],

        // Mistral
        'mistral-large-latest' => ['input' => 2.0, 'output' => 6.0],
        'mistral-small-latest' => ['input' => 0.20, 'output' => 0.60],
    ];

    /**
     * Calculate the cost in USD for a given model and token usage.
     *
     * @param  string  $model  The model identifier.
     * @param  int  $promptTokens  The number of prompt tokens.
     * @param  int  $completionTokens  The number of completion tokens.
     * @return float The estimated cost in USD.
     */
    public static function calculate(string $model, int $promptTokens, int $completionTokens): float
    {
        $pricing = self::PRICING[$model] ?? null;

        if ($pricing === null) {
            return 0.0;
        }

        $inputCost = ($promptTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $pricing['output'];

        return round($inputCost + $outputCost, 8);
    }
}
