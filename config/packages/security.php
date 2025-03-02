<?php

declare(strict_types=1);

use App\Enum\Role;
use App\Security\DiscordRequestAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $memoryProvider = $security->provider('app_agent_provider')->memory();
    $memoryProvider->user(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER)
        ->roles([Role::Agent->value]);

    $security->provider('app_user_provider')->entity()
        ->class(App\Entity\User::class)
        ->property('discordId');

    $security->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security->firewall('webhook_discord')
        ->pattern('^/webhook/discord')
        ->stateless(true)
        ->provider('app_agent_provider')
        ->customAuthenticators([DiscordRequestAuthenticator::class]);

    $security->firewall('main')
        ->lazy(true)
        ->provider('app_user_provider');

    $security->roleHierarchy(Role::Admin->value, [Role::User->value]);
    $security->roleHierarchy(Role::SuperAdmin->value, [Role::Admin->value]);
};
