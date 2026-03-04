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
        Schema::create('conductor_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->index();
            $table->string('agent_name')->index();
            $table->string('role');
            $table->longText('content');
            $table->json('tool_calls')->nullable();
            $table->json('tool_results')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductor_conversation_messages');
    }
};
