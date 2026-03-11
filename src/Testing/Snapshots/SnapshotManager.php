<?php

declare(strict_types=1);

namespace Conductor\Testing\Snapshots;

use InvalidArgumentException;

final class SnapshotManager
{
    private ?string $snapshotDirectory = null;

    /**
     * Set the directory for snapshot files.
     *
     * @param  string  $directory  The snapshot directory path.
     */
    public function directory(string $directory): static
    {
        $this->snapshotDirectory = $directory;

        return $this;
    }

    /**
     * Save a snapshot to a JSON fixture file.
     *
     * @param  string  $name  The snapshot name.
     * @param  array<string, mixed>  $data  The data to snapshot.
     */
    public function save(string $name, array $data): void
    {
        $path = $this->snapshotPath($name);
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    /**
     * Load a snapshot from a JSON fixture file.
     *
     * @param  string  $name  The snapshot name.
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public function load(string $name): array
    {
        $path = $this->snapshotPath($name);

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Snapshot not found: {$name}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidArgumentException("Unable to read snapshot: {$name}");
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Check if a snapshot exists.
     *
     * @param  string  $name  The snapshot name.
     */
    public function exists(string $name): bool
    {
        return file_exists($this->snapshotPath($name));
    }

    /**
     * Delete a snapshot.
     *
     * @param  string  $name  The snapshot name.
     */
    public function delete(string $name): void
    {
        $path = $this->snapshotPath($name);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Get the full path for a snapshot.
     *
     * @param  string  $name  The snapshot name.
     */
    private function snapshotPath(string $name): string
    {
        $directory = $this->snapshotDirectory ?? base_path('tests/__snapshots__');

        return $directory.'/'.$name.'.json';
    }
}
