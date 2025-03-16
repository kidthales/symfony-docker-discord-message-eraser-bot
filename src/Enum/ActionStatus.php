<?php

declare(strict_types=1);

namespace App\Enum;

enum ActionStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Warn = 'warn';
}
