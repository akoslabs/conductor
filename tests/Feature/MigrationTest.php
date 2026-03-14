<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('runs migrations successfully', function () {
    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('conductor_conversation_messages'))->toBeTrue()
        ->and(Schema::hasTable('conductor_workflow_runs'))->toBeTrue()
        ->and(Schema::hasTable('conductor_workflow_step_runs'))->toBeTrue()
        ->and(Schema::hasTable('conductor_usage_logs'))->toBeTrue();
});
