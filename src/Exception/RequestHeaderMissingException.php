<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

final class RequestHeaderMissingException extends Exception
{
    /**
     * @param string $header
     */
    public function __construct(string $header)
    {
        parent::__construct('Request header missing: ' . $header);
    }
}
