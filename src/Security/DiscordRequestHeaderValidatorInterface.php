<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;

interface DiscordRequestHeaderValidatorInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request): bool;
}
