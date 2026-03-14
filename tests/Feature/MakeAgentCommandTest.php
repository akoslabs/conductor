<?php

declare(strict_types=1);

it('generates an agent class from stub', function () {
    $this->artisan('make:conductor-agent', ['name' => 'TestAgent'])
        ->assertSuccessful();

    $path = app_path('Agents/TestAgent.php');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('class TestAgent extends Agent')
        ->and($content)->toContain('test-agent');

    @unlink($path);
    @rmdir(app_path('Agents'));
});
