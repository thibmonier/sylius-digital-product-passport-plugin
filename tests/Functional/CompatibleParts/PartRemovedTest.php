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
 * US-233: Gherkin scenario "Pièce retirée du catalogue (erreur)".
 *
 * Given une pièce non vendable
 * When le bloc s'affiche
 * Then elle est listée comme indisponible, sans lien d'achat
 *
 * This test MUST fail first (unavailable parts not handled yet).
 */
final class PartRemovedTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    private function createProductVariant(string $code, string $name, bool $enabled): ProductVariant
    {
        $channelRepository = static::getContainer()->get('sylius.repository.channel');
        $channel = $channelRepository->findOneBy([]);

        $product = new Product();
        $product->setCode($code);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($name);
        $product->setSlug($code);
        $product->setEnabled($enabled);
        $product->addChannel($channel);

        $variant = new ProductVariant();
        $variant->setCode($code . '-variant');
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName($name . ' Variant');
        $variant->setProduct($product);
        $variant->setEnabled($enabled);

        $channelPricing = new ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice(1000);
        $variant->addChannelPricing($channelPricing);

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);

        return $variant;
    }

    public function testUnavailablePartIsListedWithoutPurchaseLink(): void
    {
        // Given: equipment with passport
        $mainCode = 'main-equipment-' . uniqid();
        $mainVariant = $this->createProductVariant($mainCode, 'Main Equipment', true);

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($mainVariant);
        $passport->setProductIdentifier('5555555555555');
        $passport->setOriginMaterials('France');

        $this->entityManager->persist($passport);

        // Given: unavailable part (disabled)
        $unavailableCode = 'unavailable-part-' . uniqid();
        $unavailablePart = $this->createProductVariant($unavailableCode, 'Unavailable Part', false);

        $this->entityManager->flush();

        // When: page is displayed
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Then: part is listed as unavailable, without purchase link
        self::assertStringContainsString('Unavailable Part', $content);
        self::assertStringContainsString('unavailable', strtolower($content));
        self::assertStringNotContainsString('/products/' . $unavailableCode, $content);
    }
}
