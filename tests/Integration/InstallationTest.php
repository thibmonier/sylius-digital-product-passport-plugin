<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Installation test: verifies that the plugin can be installed in a Sylius application
 * without breaking anything (bundle registered, container bootable, entity mapped).
 *
 * This test MUST fail first (entity not created yet), then pass once the structure is in place.
 */
final class InstallationTest extends KernelTestCase
{
    public function testPluginBundleIsRegistered(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $bundles = $container->getParameter('kernel.bundles');
        self::assertIsArray($bundles);
        self::assertArrayHasKey('TmonierSyliusDigitalProductPassportPlugin', $bundles);
    }

    public function testContainerBoots(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertTrue($container->has('doctrine.orm.entity_manager'));
    }

    public function testDigitalProductPassportEntityIsMapped(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $metadata = $entityManager->getClassMetadata(DigitalProductPassport::class);

        self::assertSame(DigitalProductPassport::class, $metadata->getName());
        self::assertNotEmpty($metadata->getTableName());
    }
}
