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
        Schema::create('conductor_workflow_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('workflow_name')->index();
            $table->string('status')->default('pending');
            $table->json('state')->nullable();
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->string('current_step')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('total_cost_usd', 10, 6)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductor_workflow_runs');
    }
};
