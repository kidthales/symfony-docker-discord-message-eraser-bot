<?php

declare(strict_types=1);

use App\Controller\DiscordController;
use App\DependencyInjection\Parameters;
use App\Security\DiscordOAuth2Authenticator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('knpu_oauth2_client', [
        'clients' => [
            DiscordOAuth2Authenticator::REGISTRY_CLIENT_KEY => [
                'type' => 'discord',
                'client_id' => param(Parameters::DISCORD_OAUTH2_CLIENT_ID),
                'client_secret' => param(Parameters::DISCORD_OAUTH2_CLIENT_SECRET),
                'redirect_route' => DiscordController::CHECK_ROUTE_NAME,
                'redirect_params' => [],
            ],
        ],
    ]);
};
