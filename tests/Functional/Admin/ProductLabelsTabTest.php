<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests for the "Labels" tab in admin product edit page (US-232 T-232-05).
 * Gherkin scenario: QR généré (nominal).
 *
 * This test MUST fail first (tab not created yet).
 */
final class ProductLabelsTabTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    private function createAdminUser(): AdminUser
    {
        $adminUser = new AdminUser();
        $adminUser->setUsername('admin_test_' . uniqid());
        $adminUser->setEmail('admin_test_' . uniqid() . '@example.com');
        $adminUser->setPlainPassword('password123');
        $adminUser->setEnabled(true);

        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        return $adminUser;
    }

    private function createProductVariantWithGtin(string $gtin): ProductVariant
    {
        $channelRepository = static::getContainer()->get('sylius.repository.channel');
        $channel = $channelRepository->findOneBy([]);

        $product = new Product();
        $product->setCode('test-product-' . uniqid());
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName('Test Product');
        $product->setSlug('test-product-' . uniqid());
        $product->setEnabled(true);
        $product->addChannel($channel);

        $variant = new ProductVariant();
        $variant->setCode($gtin); // Use GTIN as variant code for simplicity
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName('Test Variant');
        $variant->setProduct($product);

        $channelPricing = new ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice(1000);
        $variant->addChannelPricing($channelPricing);

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);
        $this->entityManager->flush();

        return $variant;
    }

    public function testLabelsTabDisplaysGS1DigitalLinkAndQrCode(): void
    {
        // Gherkin: "QR généré (nominal)"
        // Given un produit avec GTIN
        $gtin = '01234567890128'; // Valid GTIN-14
        $variant = $this->createProductVariantWithGtin($gtin);
        $product = $variant->getProduct();

        // And admin connecté
        $adminUser = $this->createAdminUser();
        $this->client->loginUser($adminUser);

        $productId = $product->getId();
        $this->entityManager->clear();

        // When Sophie ouvre l'onglet « Étiquettes »
        $this->client->request('GET', '/admin/products/' . $productId . '/edit');

        // Then la page se charge sans erreur
        self::assertResponseIsSuccessful();

        // And un lien GS1 Digital Link est proposé
        $expectedLink = 'https://id.gs1.org/01/' . $gtin;
        self::assertSelectorTextContains('body', $expectedLink);

        // And un QR est proposé (SVG element present)
        self::assertSelectorExists('svg');
    }

    public function testLabelsTabShowsInvalidGtinError(): void
    {
        // Gherkin: "GTIN absent ou invalide (erreur)"
        // Given un GTIN invalide (clé de contrôle fausse)
        $invalidGtin = '01234567890127'; // Invalid check digit (should be 8)
        $variant = $this->createProductVariantWithGtin($invalidGtin);
        $product = $variant->getProduct();

        // And admin connecté
        $adminUser = $this->createAdminUser();
        $this->client->loginUser($adminUser);

        $productId = $product->getId();
        $this->entityManager->clear();

        // When Sophie génère le lien (opens the tab)
        $this->client->request('GET', '/admin/products/' . $productId . '/edit');

        // Then la page se charge
        self::assertResponseIsSuccessful();

        // And une erreur explique la clé de contrôle
        self::assertSelectorTextContains('.alert-warning', 'Invalid GTIN');
        self::assertSelectorTextContains('.alert-warning', $invalidGtin);
    }
}
