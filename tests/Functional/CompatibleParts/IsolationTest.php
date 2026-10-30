<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\CompatibleParts;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\CustomerAsset;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModel;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-233: Gherkin scenario "Cloisonnement (erreur)".
 *
 * Given le parc d'une autre entreprise
 * When Claire ouvre son passeport
 * Then aucune donnée de l'autre parc n'apparaît
 *
 * The public passport page displays only catalogue data (product names, codes).
 * NO customer-specific data (CustomerAsset label/identifier) should appear, even if
 * assets exist for the equipment model.
 */
final class IsolationTest extends WebTestCase
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

    private function createCustomer(string $email): Customer
    {
        $customer = new Customer();
        $customer->setEmail($email);
        $customer->setFirstName('Test');
        $customer->setLastName('User');

        $this->entityManager->persist($customer);

        return $customer;
    }

    public function testCustomerAssetDataDoesNotAppearOnPublicPage(): void
    {
        // Given: EquipmentModel with 1 compatible part
        $modelCode = 'TRUCK-MODEL-' . uniqid();
        $model = new EquipmentModel();
        $model->setCode($modelCode);
        $model->setCurrentLocale('en_US');
        $model->setFallbackLocale('en_US');
        $model->setName('Truck Model');

        $partCode = 'acme-part-' . uniqid();
        $part = $this->createProductVariant($partCode, 'Acme Part');

        $model->addCompatibleVariant($part);
        $this->entityManager->persist($model);

        // Given: CustomerAssets for two different customers (Acme + Other)
        $customerAcme = $this->createCustomer('acme@example.com');
        $customerOther = $this->createCustomer('other@example.com');

        $assetAcme = new CustomerAsset();
        $assetAcme->setCustomer($customerAcme);
        $assetAcme->setEquipmentModel($model);
        $assetAcme->setLabel('ACME-TRUCK-001'); // MUST NOT appear
        $assetAcme->setIdentifier('AB-123-CD'); // MUST NOT appear

        $assetOther = new CustomerAsset();
        $assetOther->setCustomer($customerOther);
        $assetOther->setEquipmentModel($model);
        $assetOther->setLabel('OTHER-TRUCK-999'); // MUST NOT appear
        $assetOther->setIdentifier('XY-789-ZW'); // MUST NOT appear

        $this->entityManager->persist($assetAcme);
        $this->entityManager->persist($assetOther);

        // Given: Passport linked to model
        $mainCode = 'main-equipment-' . uniqid();
        $mainVariant = $this->createProductVariant($mainCode, 'Main Equipment');

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($mainVariant);
        $passport->setOriginMaterials('France');
        $passport->setEquipmentModelCode($modelCode);

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        // When: Public page is displayed
        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Then: Compatible part IS displayed (catalogue data)
        self::assertStringContainsString('Acme Part', $content);
        self::assertStringContainsString($partCode, $content);

        // Then: CustomerAsset data MUST NOT appear (isolation)
        self::assertStringNotContainsString('ACME-TRUCK-001', $content);
        self::assertStringNotContainsString('AB-123-CD', $content);
        self::assertStringNotContainsString('OTHER-TRUCK-999', $content);
        self::assertStringNotContainsString('XY-789-ZW', $content);
    }
}
