<?php

declare(strict_types=1);

use Conductor\Testing\FakeAgentResponse;
use Conductor\Testing\Snapshots\SnapshotManager;

it('can snapshot and replay agent responses', function () {
    $manager = new SnapshotManager;
    $dir = sys_get_temp_dir().'/conductor-snapshot-test-'.uniqid();
    $manager->directory($dir);

    // Create a fake response and snapshot it
    $response = new FakeAgentResponse(
        text: 'Snapshot response',
        promptTokens: 100,
        completionTokens: 50,
    );

    $manager->save('agent-response', $response->toArray());

    // Load and recreate response from snapshot
    $data = $manager->load('agent-response');
    $replayed = FakeAgentResponse::fromArray($data);

    expect($replayed->text())->toBe('Snapshot response')
        ->and($replayed->promptTokens())->toBe(100)
        ->and($replayed->completionTokens())->toBe(50);

    $manager->delete('agent-response');
    @rmdir($dir);
});
