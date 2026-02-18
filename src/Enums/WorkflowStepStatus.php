<?php

declare(strict_types=1);

namespace Conductor\Enums;

enum WorkflowStepStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case PausedForHuman = 'paused_for_human';
}
