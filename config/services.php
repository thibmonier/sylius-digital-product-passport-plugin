<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->load('Tmonier\\SyliusDigitalProductPassportPlugin\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/DependencyInjection',
            __DIR__ . '/../src/Entity',
            __DIR__ . '/../src/Migrations',
        ]);
};
