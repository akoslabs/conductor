<?php

declare(strict_types=1);

namespace Conductor\Enums;

enum MemoryDriver: string
{
    case Database = 'database';
    case Cache = 'cache';
    case Array_ = 'array';
}
