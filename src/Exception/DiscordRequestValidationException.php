<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

final class DiscordRequestValidationException extends Exception
{
    /**
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(message: 'Error occurred during Discord request header validation', previous: $previous);
    }
}
