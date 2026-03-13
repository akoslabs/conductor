<?php

declare(strict_types=1);

use Conductor\Testing\Snapshots\SnapshotManager;

it('saves and loads a snapshot', function () {
    $manager = new SnapshotManager;
    $dir = sys_get_temp_dir().'/conductor-test-snapshots-'.uniqid();
    $manager->directory($dir);

    $data = ['text' => 'Hello', 'tokens' => 42];

    $manager->save('test-snapshot', $data);

    expect($manager->exists('test-snapshot'))->toBeTrue();

    $loaded = $manager->load('test-snapshot');
    expect($loaded)->toBe($data);

    $manager->delete('test-snapshot');
    expect($manager->exists('test-snapshot'))->toBeFalse();

    @rmdir($dir);
});

it('throws when loading nonexistent snapshot', function () {
    $manager = new SnapshotManager;
    $manager->directory(sys_get_temp_dir().'/nonexistent-'.uniqid());

    $manager->load('does-not-exist');
})->throws(InvalidArgumentException::class, 'Snapshot not found');
