<?php

declare(strict_types=1);

namespace Conductor\Agents\Concerns;

trait HasFallbacks
{
    /** @var array<int, array{provider: string, model: string}> */
    protected array $fallbacks = [];

    /**
     * Add a fallback provider and model.
     *
     * @param  string  $provider  The fallback provider.
     * @param  string  $model  The fallback model.
     */
    public function addFallback(string $provider, string $model): static
    {
        $this->fallbacks[] = ['provider' => $provider, 'model' => $model];

        return $this;
    }

    /**
     * Get fallback configurations from config.
     *
     * @return array<int, array{provider: string, model: string}>
     */
    protected function getConfigFallbacks(): array
    {
        return config('conductor.fallbacks', []);
    }

    /**
     * Get all fallbacks (builder + config).
     *
     * @return array<int, array{provider: string, model: string}>
     */
    protected function getAllFallbacks(): array
    {
        return array_merge($this->fallbacks, $this->getConfigFallbacks());
    }
}
