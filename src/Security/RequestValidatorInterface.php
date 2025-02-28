<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

interface RequestValidatorInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request): bool;
}
