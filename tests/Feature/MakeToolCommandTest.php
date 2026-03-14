<?php

declare(strict_types=1);

it('generates a tool class from stub', function () {
    $this->artisan('make:conductor-tool', ['name' => 'SearchTool'])
        ->assertSuccessful();

    $path = app_path('Tools/SearchTool.php');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('class SearchTool extends Tool')
        ->and($content)->toContain('search-tool');

    @unlink($path);
    @rmdir(app_path('Tools'));
});
