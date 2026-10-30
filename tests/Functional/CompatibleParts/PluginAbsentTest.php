<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\CompatibleParts;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-233: Gherkin scenario "Plugin parc absent (alternatif)".
 *
 * Given un projet sans customer-asset-management
 * When la page publique s'affiche
 * Then le bloc est absent, sans erreur
 *
 * This is a non-regression guard test (may pass before code).
 */
final class PluginAbsentTest extends WebTestCase
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
        $variant->setCode('test-variant-' . uniqid());
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

    public function testPageDisplaysWithoutErrorWhenPluginIsAbsent(): void
    {
        // Given: un projet sans customer-asset-management (default test environment)
        $variant = $this->createProductVariant();

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setProductIdentifier('9876543210987');
        $passport->setOriginMaterials('France');

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // When: la page publique s'affiche
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();

        // Then: le bloc est absent, sans erreur (200 OK)
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Verify the compatible parts section is NOT displayed
        self::assertStringNotContainsString('Compatible parts', $content);
        self::assertStringNotContainsString('Pièces compatibles', $content);
    }
}
