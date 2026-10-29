<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Tests for JSON export (US-230 T-230-05).
 * Gherkin scenario: "Export JSON (alternatif)"
 *
 * This test MUST fail first (export method not created yet).
 */
final class PassportJsonExportTest extends KernelTestCase
{
    private function createProductVariant(EntityManagerInterface $entityManager): ProductVariant
    {
        $product = new Product();
        $product->setCode('test-product-' . uniqid());
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName('Test Product');
        $product->setSlug('test-product-' . uniqid());

        $variant = new ProductVariant();
        $variant->setCode('test-variant-' . uniqid());
        $variant->setProduct($product);

        $entityManager->persist($product);
        $entityManager->persist($variant);
        $entityManager->flush();

        return $variant;
    }

    public function testExportToJsonReturnsValidSchema(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $variant = $this->createProductVariant($entityManager);

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setProductIdentifier('12345678901234');
        $passport->setOriginMaterials('France, Allemagne');
        $passport->setRecyclability('100% recyclable');
        $passport->setRepairability('Indice de réparabilité : 8/10');

        $entityManager->persist($passport);
        $entityManager->flush();

        // Export to JSON (method not yet created - this MUST fail)
        $json = $passport->toJson();

        $data = json_decode($json, true);
        self::assertIsArray($data);

        // Verify schema version
        self::assertArrayHasKey('schemaVersion', $data);
        self::assertSame(1, $data['schemaVersion']);

        // Verify required fields
        self::assertArrayHasKey('productIdentifier', $data);
        self::assertArrayHasKey('originMaterials', $data);
        self::assertArrayHasKey('recyclability', $data);
        self::assertArrayHasKey('repairability', $data);
        self::assertArrayHasKey('version', $data);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);

        // Verify values
        self::assertSame('12345678901234', $data['productIdentifier']);
        self::assertSame('France, Allemagne', $data['originMaterials']);
        self::assertSame('100% recyclable', $data['recyclability']);
        self::assertSame('Indice de réparabilité : 8/10', $data['repairability']);
        self::assertSame(1, $data['version']);
    }
}
