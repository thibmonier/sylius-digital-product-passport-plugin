<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\DigitalLinkGenerator;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\GtinValidator;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\QrGenerator;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\PassportRevisionService;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\PassportRevisionServiceInterface;

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

    $services->set('tmonier_digital_product_passport.revision_service', PassportRevisionService::class)
        ->public()
        ->alias(PassportRevisionServiceInterface::class, 'tmonier_digital_product_passport.revision_service')
        ->public();

    // GS1 Digital Link services (US-232)
    $services->set(GtinValidator::class)->public();
    $services->set(DigitalLinkGenerator::class)->public();
    $services->set(QrGenerator::class)->public();
};
