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
 * US-233: Gherkin scenario "Pièces affichées (nominal)".
 *
 * Given un équipement avec 3 pièces compatibles
 * When Claire ouvre son passeport
 * Then les 3 pièces s'affichent avec lien vers leur fiche
 *
 * This test MUST fail first (compatible parts not displayed yet).
 */
final class PartsDisplayedTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    private function createProductVariant(string $code, string $name): ProductVariant
    {
        $channelRepository = static::getContainer()->get('sylius.repository.channel');
        $channel = $channelRepository->findOneBy([]);

        $product = new Product();
        $product->setCode($code);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($name);
        $product->setSlug($code);
        $product->setEnabled(true);
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

    public function testThreeCompatiblePartsAreDisplayedWithLinks(): void
    {
        // Given: EquipmentModel avec 3 pièces compatibles
        $modelCode = 'TRUCK-MODEL-' . uniqid();
        $model = new EquipmentModel();
        $model->setCode($modelCode);
        $model->setCurrentLocale('en_US');
        $model->setFallbackLocale('en_US');
        $model->setName('Truck Model');

        $part1Code = 'spare-part-1-' . uniqid();
        $part2Code = 'spare-part-2-' . uniqid();
        $part3Code = 'spare-part-3-' . uniqid();

        $part1 = $this->createProductVariant($part1Code, 'Spare Part 1');
        $part2 = $this->createProductVariant($part2Code, 'Spare Part 2');
        $part3 = $this->createProductVariant($part3Code, 'Spare Part 3');

        $model->addCompatibleVariant($part1);
        $model->addCompatibleVariant($part2);
        $model->addCompatibleVariant($part3);

        $this->entityManager->persist($model);

        // Given: Passport lié au modèle
        $mainCode = 'main-equipment-' . uniqid();
        $mainVariant = $this->createProductVariant($mainCode, 'Main Equipment');

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($mainVariant);
        $passport->setProductIdentifier('1234567890123');
        $passport->setOriginMaterials('France');
        $passport->setEquipmentModelCode($modelCode);

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // When: Claire ouvre le passeport
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Then: compatible parts block present
        self::assertSelectorExists('[data-test="passport-compatible-parts"]');

        // Then: les 3 pièces s'affichent avec lien vers leur fiche
        self::assertStringContainsString('Spare Part 1', $content);
        self::assertStringContainsString('Spare Part 2', $content);
        self::assertStringContainsString('Spare Part 3', $content);

        // Verify links to product pages
        self::assertStringContainsString('/products/' . $part1Code, $content);
        self::assertStringContainsString('/products/' . $part2Code, $content);
        self::assertStringContainsString('/products/' . $part3Code, $content);
    }
}
