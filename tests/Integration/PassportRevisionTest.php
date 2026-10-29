<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\PassportRevisionServiceInterface;

/**
 * Tests for passport revision service (US-230 T-230-04).
 * Gherkin scenario: "Révision (alternatif)"
 *
 * These tests MUST fail first (service not created yet).
 */
final class PassportRevisionTest extends KernelTestCase
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

    public function testRevisionIsCreatedWhenPassportIsModified(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        /** @var PassportRevisionServiceInterface $revisionService */
        $revisionService = $container->get('tmonier_digital_product_passport.revision_service');

        $variant = $this->createProductVariant($entityManager);

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setOriginMaterials('France');

        // Create first revision
        $revisionService->createRevision($passport, 'author@example.com');

        // Modify passport
        $passport->setOriginMaterials('Germany');

        // Create second revision
        $revisionService->createRevision($passport, 'author@example.com');

        $revisions = $revisionService->getRevisions($passport);

        self::assertCount(2, $revisions);
    }

    public function testRevisionContainsDateAndAuthor(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        /** @var PassportRevisionServiceInterface $revisionService */
        $revisionService = $container->get('tmonier_digital_product_passport.revision_service');

        $variant = $this->createProductVariant($entityManager);

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setOriginMaterials('France');

        $revisionService->createRevision($passport, 'alice@example.com');

        $revisions = $revisionService->getRevisions($passport);
        $revision = $revisions[0];

        self::assertSame('alice@example.com', $revision->getAuthor());
        self::assertInstanceOf(\DateTimeImmutable::class, $revision->getCreatedAt());
    }
}
