<?php

declare(strict_types=1);

use App\Security\DiscordRequestAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $memoryProvider = $security->provider('app_agent_user_provider')->memory();
    $memoryProvider->user(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER);

    $security->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security->firewall('discord')
        ->pattern('^/webhook/discord')
        ->stateless(true)
        ->provider('app_agent_user_provider')
        ->customAuthenticators([DiscordRequestAuthenticator::class]);

    $security->firewall('main')
        ->lazy(true);
};
