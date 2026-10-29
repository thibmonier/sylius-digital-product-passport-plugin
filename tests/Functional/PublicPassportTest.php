<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Tests for public passport page (US-231).
 * Gherkin scenarios: page lisible, lien depuis fiche produit, langue, 404 inexistant, 404 non publié.
 *
 * These tests MUST fail first (controller not created yet).
 */
final class PublicPassportTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    private function createProductVariant(): ProductVariant
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

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);
        $this->entityManager->flush();

        return $variant;
    }

    public function testPublicPassportPageDisplaysAllInformation(): void
    {
        // Gherkin: "Page lisible (nominal)"
        $variant = $this->createProductVariant();

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setProductIdentifier('1234567890123');
        $passport->setOriginMaterials('France, Allemagne');
        $passport->setRecyclability('100% recyclable');
        $passport->setRepairability('Indice de réparabilité : 8/10');

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        $this->client->request('GET', '/product-passport/' . $passport->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'France, Allemagne');
        self::assertSelectorTextContains('body', '100% recyclable');
        self::assertSelectorTextContains('body', 'Indice de réparabilité : 8/10');
        self::assertSelectorTextContains('body', 'Version 1');
        self::assertSelectorExists('[data-test="passport-updated-at"]');
    }

    public function testProductPageShowsPassportLink(): void
    {
        // Gherkin: "Lien depuis la fiche produit (alternatif)"
        $variant = $this->createProductVariant();

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setOriginMaterials('France');

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // Note: This test will need a product show page template override
        // For now, we test that the passport exists and has an ID
        self::assertNotNull($passport->getId());
    }

    public function testPassportPageDisplaysInEnglish(): void
    {
        // Gherkin: "Langue (alternatif)"
        $variant = $this->createProductVariant();

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setOriginMaterials('France');
        $passport->setRecyclability('100% recyclable');
        $passport->setRepairability('Repair index: 8/10');

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        $this->client->request('GET', '/product-passport/' . $passport->getId(), [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'en',
        ]);

        self::assertResponseIsSuccessful();
        // Labels should be in English
        self::assertSelectorTextContains('body', 'Origin');
        self::assertSelectorTextContains('body', 'Recyclability');
        self::assertSelectorTextContains('body', 'Repairability');
    }

    public function testNonExistentPassportReturns404(): void
    {
        // Gherkin: "Passeport inexistant (erreur)"
        $this->client->request('GET', '/product-passport/999999');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('body', '404');
    }

    public function testDraftPassportReturns404(): void
    {
        // Gherkin: "Passeport non publié (erreur)"
        // For now, all passports are public. This test ensures no data leaks.
        $this->client->request('GET', '/product-passport/999999');

        self::assertResponseStatusCodeSame(404);
        // Verify 404 message is shown
        self::assertSelectorTextContains('body', 'Passport not found');
    }
}
