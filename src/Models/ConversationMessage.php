<?php

declare(strict_types=1);

namespace Conductor\Models;

use Illuminate\Database\Eloquent\Model;

final class ConversationMessage extends Model
{
    /** @var string */
    protected $table = 'conductor_conversation_messages';

    /** @var array<int, string> */
    protected $fillable = [
        'conversation_id',
        'agent_name',
        'role',
        'content',
        'tool_calls',
        'tool_results',
        'metadata',
        'prompt_tokens',
        'completion_tokens',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tool_calls' => 'array',
            'tool_results' => 'array',
            'metadata' => 'array',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
        ];
    }
}
