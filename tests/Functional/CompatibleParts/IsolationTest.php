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
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-233: Gherkin scenario "Cloisonnement (erreur)".
 *
 * Given le parc d'une autre entreprise
 * When Claire ouvre son passeport
 * Then aucune donnée de l'autre parc n'apparaît
 *
 * This test MUST fail first (isolation not implemented yet).
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
        $customer->setLastName('Customer');

        $this->entityManager->persist($customer);

        return $customer;
    }

    public function testNoDataFromOtherCompanyIsDisplayed(): void
    {
        // Given: Claire's company
        $claire = $this->createCustomer('claire@acme.com');

        // Given: Another company
        $otherCustomer = $this->createCustomer('other@competitor.com');

        // Given: Equipment with passport
        $mainCode = 'main-equipment-' . uniqid();
        $mainVariant = $this->createProductVariant($mainCode, 'Main Equipment');

        $passport = new DigitalProductPassport();
        $passport->setProductVariant($mainVariant);
        $passport->setProductIdentifier('1111111111111');
        $passport->setOriginMaterials('France');

        $this->entityManager->persist($passport);

        // Given: Part from other company's fleet (should NOT be displayed to Claire)
        $otherPartCode = 'other-company-part-' . uniqid();
        $otherPart = $this->createProductVariant($otherPartCode, 'Other Company Part');

        $this->entityManager->flush();

        // When: Claire opens the passport (logged in as Claire)
        $claireUser = $this->createUser($claire);
        $this->client->loginUser($claireUser, 'shop');

        $this->client->request('GET', '/product-passport/' . $passport->getId());

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();

        $content = $response->getContent();
        self::assertIsString($content);

        // Then: aucune donnée de l'autre parc n'apparaît
        self::assertStringNotContainsString('Other Company Part', $content);
        self::assertStringNotContainsString($otherPartCode, $content);
    }

    private function createUser(Customer $customer): \Sylius\Component\Core\Model\ShopUserInterface
    {
        $userFactory = static::getContainer()->get('sylius.factory.shop_user');
        $user = $userFactory->createNew();
        \assert($user instanceof \Sylius\Component\Core\Model\ShopUserInterface);

        $user->setUsername($customer->getEmail());
        $user->setPlainPassword('password123');
        $user->setEnabled(true);
        $user->setCustomer($customer);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
