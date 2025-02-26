<?php

declare(strict_types=1);

use App\DependencyInjection\Parameters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();

    $parameters->set(Parameters::DISCORD_APP_PUBLIC_KEY, '%env(default::string:DISCORD_APP_PUBLIC_KEY)%');

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/DependencyInjection/',
            __DIR__ . '/../src/Entity/',
            __DIR__ . '/../src/Exception/',
            __DIR__ . '/../src/Kernel.php',
        ]);

    $parameters->set('.container.dumper.inline_factories', true);
};
