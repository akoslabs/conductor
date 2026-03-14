<?php

declare(strict_types=1);

it('generates a workflow class from stub', function () {
    $this->artisan('make:conductor-workflow', ['name' => 'ContentPipeline'])
        ->assertSuccessful();

    $path = app_path('Workflows/ContentPipeline.php');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('class ContentPipeline extends Workflow')
        ->and($content)->toContain('content-pipeline');

    @unlink($path);
    @rmdir(app_path('Workflows'));
});
