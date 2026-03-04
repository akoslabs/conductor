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
        Schema::create('conductor_workflow_step_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_run_id')->index();
            $table->string('step_name');
            $table->string('status')->default('pending');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->unsignedInteger('attempt')->default(1);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('workflow_run_id')
                ->references('id')
                ->on('conductor_workflow_runs')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductor_workflow_step_runs');
    }
};
