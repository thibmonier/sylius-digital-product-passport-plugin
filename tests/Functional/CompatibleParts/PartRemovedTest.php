<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\CompatibleParts;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModel;
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

    private function createProductVariant(string $code, string $name, bool $enabled = true): ProductVariant
    {
        $channelRepository = static::getContainer()->get('sylius.repository.channel');
        $channel = $channelRepository->findOneBy([]);

        $product = new Product();
        $product->setCode($code);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($name);
        $product->setSlug($code);
        $product->setEnabled($enabled); // Can be disabled
        $product->addChannel($channel);

        $variant = new ProductVariant();
        $variant->setCode($code . '-variant');
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName($name . ' Variant');
        $variant->setProduct($product);

        $channelPricing = new ChannelPricing();
        $channelPricing->setChannelCode($channel->getCode());
        $channelPricing->setPrice(1000);
        $variant->addChannelPricing($channelPricing);

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);

        return $variant;
    }

    public function testUnavailablePartListedWithoutPurchaseLink(): void
    {
        // Given: EquipmentModel with 1 unavailable part
        $modelCode = 'TRUCK-MODEL-' . uniqid();
        $model = new EquipmentModel();
        $model->setCode($modelCode);
        $model->setCurrentLocale('en_US');
        $model->setFallbackLocale('en_US');
        $model->setName('Truck Model');

        $unavailablePartCode = 'unavailable-part-' . uniqid();
        $unavailablePart = $this->createProductVariant($unavailablePartCode, 'Unavailable Part', false); // disabled

        $model->addCompatibleVariant($unavailablePart);
        $this->entityManager->persist($model);

        // Given: Passport linked to model
        $mainCode = 'main-equipment-' . uniqid();
        $mainVariant = $this->createProductVariant($mainCode, 'Main Equipment');

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($mainVariant);
        $passport->setOriginMaterials('France');
        $passport->setEquipmentModelCode($modelCode);

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // When: bloc s'affiche
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Then: pièce listée comme indisponible
        self::assertStringContainsString('Unavailable Part', $content);
        self::assertStringContainsString('unavailable', $content);

        // sans lien d'achat
        self::assertStringNotContainsString('/products/' . $unavailablePartCode, $content);
    }
}
