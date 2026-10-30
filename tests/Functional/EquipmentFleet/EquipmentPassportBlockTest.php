<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\EquipmentFleet;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\CustomerAsset;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModel;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * Tests for the equipment passport block on the equipment details page (US-242).
 * Gherkin scenarios: fiche avec passeport, modèle sans passeport, équipement d'une autre entreprise.
 *
 * These tests MUST fail first (hook template not created yet).
 */
final class EquipmentPassportBlockTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
    }

    private function createShopUser(string $email): ShopUser
    {
        $customer = new Customer();
        $customer->setEmail($email);
        $customer->setFirstName('Test');
        $customer->setLastName('User');

        $shopUser = new ShopUser();
        $shopUser->setUsername($email);
        $shopUser->setPlainPassword('password123');
        $shopUser->setEnabled(true);
        $shopUser->setCustomer($customer);

        $this->entityManager->persist($customer);
        $this->entityManager->persist($shopUser);
        $this->entityManager->flush();

        return $shopUser;
    }

    private function createProductVariant(string $name): ProductVariant
    {
        $channel = static::getContainer()->get('sylius.repository.channel')->findOneBy([]);
        \assert($channel instanceof ChannelInterface);
        $code = 'part-' . uniqid();

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
        $variant->setName($name);
        $variant->setProduct($product);

        $pricing = new ChannelPricing();
        $pricing->setChannelCode((string) $channel->getCode());
        $pricing->setPrice(1000);
        $variant->addChannelPricing($pricing);

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);

        return $variant;
    }

    private function createEquipmentModel(string $code, ?ProductVariant $compatiblePart = null): EquipmentModel
    {
        $model = new EquipmentModel();
        $model->setCode($code);
        $model->setCurrentLocale('en_US');
        $model->setFallbackLocale('en_US');
        $model->setName('Test Equipment Model');
        if (null !== $compatiblePart) {
            $model->addCompatibleVariant($compatiblePart);
        }

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $model;
    }

    private function createCustomerAsset(Customer $customer, EquipmentModel $model, string $label): CustomerAsset
    {
        $asset = new CustomerAsset();
        $asset->setCustomer($customer);
        $asset->setEquipmentModel($model);
        $asset->setLabel($label);
        $asset->setIdentifier('SN-' . uniqid());

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    public function testEquipmentPageDisplaysPassportLinkWhenModelHasPassport(): void
    {
        // Gherkin: "Fiche avec passeport (nominal)"
        $shopUser = $this->createShopUser('test_' . uniqid() . '@example.com');
        $customer = $shopUser->getCustomer();

        $model = $this->createEquipmentModel('MODEL-' . strtoupper(uniqid()), $this->createProductVariant('Compatible Filter'));
        $asset = $this->createCustomerAsset($customer, $model, 'Machine de test');

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($this->createProductVariant('Machine Model Variant'));
        $passport->setEquipmentModelCode($model->getCode());
        $passport->setProductIdentifier('1234567890123');
        $passport->setOriginMaterials('Germany');
        $passport->setRecyclability('95% recyclable');
        $passport->setRepairability('Index: 8/10');

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        $this->entityManager->clear();

        $this->client->loginUser($shopUser, 'shop');
        $this->client->request('GET', '/en_US/account/equipment/' . $asset->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test="equipment-passport-block"]');
        self::assertSelectorExists('[data-test="equipment-passport-link"]');
        self::assertSelectorExists('[data-test="equipment-compatible-parts"]');
        self::assertSelectorTextContains('[data-test="equipment-compatible-parts"]', 'Compatible Filter');
    }

    public function testEquipmentPageShowsNoPassportBlockWhenModelHasNoPassport(): void
    {
        // Gherkin: "Modèle sans passeport (alternatif)"
        $shopUser = $this->createShopUser('test_' . uniqid() . '@example.com');
        $customer = $shopUser->getCustomer();

        $model = $this->createEquipmentModel('MODEL-NO-PASSPORT-' . strtoupper(uniqid()));
        $asset = $this->createCustomerAsset($customer, $model, 'Machine sans passeport');

        $this->entityManager->clear();

        $this->client->loginUser($shopUser, 'shop');
        $this->client->request('GET', '/en_US/account/equipment/' . $asset->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-test="equipment-passport-block"]');
    }

    public function testEquipmentFromAnotherCompanyReturns404(): void
    {
        // Gherkin: "Équipement d'une autre entreprise (erreur)"
        $shopUser1 = $this->createShopUser('user1_' . uniqid() . '@example.com');
        $shopUser2 = $this->createShopUser('user2_' . uniqid() . '@example.com');
        $customer2 = $shopUser2->getCustomer();

        $model = $this->createEquipmentModel('MODEL-' . strtoupper(uniqid()));
        $asset = $this->createCustomerAsset($customer2, $model, 'Machine du client 2');

        $this->entityManager->clear();

        $this->client->loginUser($shopUser1, 'shop');
        $this->client->request('GET', '/en_US/account/equipment/' . $asset->getId());

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorNotExists('[data-test="equipment-passport-block"]');
    }
}
