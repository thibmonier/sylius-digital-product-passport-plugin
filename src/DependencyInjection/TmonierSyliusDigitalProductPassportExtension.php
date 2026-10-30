<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Yaml\Yaml;

final class TmonierSyliusDigitalProductPassportExtension extends Extension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'TmonierSyliusDigitalProductPassportPlugin' => [
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/../Entity',
                        'prefix' => 'Tmonier\SyliusDigitalProductPassportPlugin\Entity',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);

        $this->prependDoctrineMigrations($container);

        /** @var array{sylius_twig_hooks: array<string, mixed>} $twigHooksConfig */
        $twigHooksConfig = Yaml::parseFile(__DIR__ . '/../../config/twig_hooks/product_show.yaml');
        $container->prependExtensionConfig('sylius_twig_hooks', $twigHooksConfig['sylius_twig_hooks']);

        /** @var array{sylius_twig_hooks: array<string, mixed>} $adminProductUpdateConfig */
        $adminProductUpdateConfig = Yaml::parseFile(__DIR__ . '/../../config/twig_hooks/product_update.yaml');
        $container->prependExtensionConfig('sylius_twig_hooks', $adminProductUpdateConfig['sylius_twig_hooks']);

        /** @var array{sylius_twig_hooks: array<string, mixed>} $equipmentShowConfig */
        $equipmentShowConfig = Yaml::parseFile(__DIR__ . '/../../config/twig_hooks/equipment_show.yaml');
        $container->prependExtensionConfig('sylius_twig_hooks', $equipmentShowConfig['sylius_twig_hooks']);
    }

    public function getAlias(): string
    {
        return 'tmonier_sylius_digital_product_passport';
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Tmonier\SyliusDigitalProductPassportPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@TmonierSyliusDigitalProductPassportPlugin/src/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [
            'Sylius\Bundle\CoreBundle\Migrations',
        ];
    }
}
