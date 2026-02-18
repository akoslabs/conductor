<?php

declare(strict_types=1);

namespace Conductor\Enums;

enum AgentStatus: string
{
    case Idle = 'idle';
    case Running = 'running';
    case WaitingForTool = 'waiting_for_tool';
    case Completed = 'completed';
    case Failed = 'failed';
}
