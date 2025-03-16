<?php

declare(strict_types=1);

use App\Enum\Role;
use App\Security\DiscordRequestAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $memoryProvider = $security->provider('agent_provider')->memory();
    $memoryProvider->user(DiscordRequestAuthenticator::AGENT_USER_IDENTIFIER)
        ->roles([Role::Agent->value]);
    $memoryProvider->user('agent:cli')
        ->roles([Role::Agent->value, Role::SuperAdmin->value]);

    $security->provider('user_provider')->entity()
        ->class(App\Entity\User::class)
        ->property('discordId');

    $security->provider('all_provider')->chain()
        ->providers(['agent_provider', 'user_provider']);

    $security->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security->firewall('webhook_discord')
        ->pattern('^/webhook/discord')
        ->stateless(true)
        ->provider('agent_provider')
        ->customAuthenticators([DiscordRequestAuthenticator::class]);

    $mainFirewall = $security->firewall('main')
        ->lazy(true)
        ->provider('user_provider')
        ->entryPoint(App\Security\AuthenticationEntryPoint::class)
        ->customAuthenticators([App\Security\DiscordOAuth2Authenticator::class]);
    $mainFirewall->logout()
        ->path('/logout')
        ->target('app_logout'); // TODO

    $security->roleHierarchy(Role::Admin->value, [Role::User->value]);
    $security->roleHierarchy(Role::SuperAdmin->value, [Role::Admin->value]);
};
