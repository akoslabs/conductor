<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conductor_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name')->index();
            $table->string('provider');
            $table->string('model');
            $table->unsignedInteger('prompt_tokens');
            $table->unsignedInteger('completion_tokens');
            $table->decimal('cost_usd', 10, 6);
            $table->unsignedInteger('duration_ms');
            $table->string('workflow_run_id')->nullable()->index();
            $table->string('workflow_step')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductor_usage_logs');
    }
};
