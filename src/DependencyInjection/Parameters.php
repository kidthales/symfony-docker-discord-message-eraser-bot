<?php

declare(strict_types=1);

namespace App\DependencyInjection;

final readonly class Parameters
{
    public const string DISCORD_APP_PUBLIC_KEY = 'app.discord_app_public_key';

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
