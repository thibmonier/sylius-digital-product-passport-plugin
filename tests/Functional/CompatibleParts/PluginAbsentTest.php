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
 * This test verifies that a passport WITHOUT equipmentModelCode does not display
 * the compatible parts block (plugin cannot be uninstalled in CI).
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

        return $variant;
    }

    public function testCompatiblePartsBlockAbsentWithoutEquipmentModelCode(): void
    {
        // Given: Passport WITHOUT equipmentModelCode
        $variant = $this->createProductVariant();

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($variant);
        $passport->setOriginMaterials('France');
        // equipmentModelCode is NOT set

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // When: Page publique s'affiche
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        // Then: compatible parts block absent
        self::assertSelectorNotExists('[data-test="passport-compatible-parts"]');
    }
}
