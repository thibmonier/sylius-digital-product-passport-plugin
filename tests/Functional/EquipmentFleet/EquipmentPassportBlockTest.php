<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\EquipmentFleet;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\ShopUser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\CustomerAsset;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModel;
use Tmonier\SyliusCustomerAssetManagementPlugin\Entity\EquipmentModelTranslation;
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

    private function createEquipmentModel(string $code): EquipmentModel
    {
        $model = new EquipmentModel();
        $model->setCode($code);
        $model->setCurrentLocale('en_US');
        $model->setFallbackLocale('en_US');
        $model->setName('Test Equipment Model');

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

        $model = $this->createEquipmentModel('MODEL-' . strtoupper(uniqid()));
        $asset = $this->createCustomerAsset($customer, $model, 'Machine de test');

        $passport = new DigitalProductPassport();
        $passport->setEquipmentModelCode($model->getCode());
        $passport->setProductIdentifier('EQ-' . uniqid());
        $passport->setOriginMaterials('Germany');
        $passport->setRecyclability('95% recyclable');
        $passport->setRepairability('Index: 8/10');
        $passport->setPublished(true);

        $this->entityManager->persist($passport);
        $this->entityManager->flush();

        $this->entityManager->clear();

        $this->client->loginUser($shopUser, 'shop');
        $this->client->request('GET', '/en_US/account/equipment/' . $asset->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test="equipment-passport-block"]');
        self::assertSelectorExists('[data-test="equipment-passport-link"]');
        self::assertSelectorExists('[data-test="equipment-compatible-parts"]');
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
