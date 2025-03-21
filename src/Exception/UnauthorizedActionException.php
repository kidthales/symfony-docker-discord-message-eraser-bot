<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

final class UnauthorizedActionException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "Unauthorized", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
