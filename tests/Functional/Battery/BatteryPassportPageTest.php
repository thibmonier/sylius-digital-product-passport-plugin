<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\Battery;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tmonier\SyliusDigitalProductPassportPlugin\Support\BatteryFixtureTrait;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryCategory;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;

/**
 * US-284: battery section of the public passport page.
 */
final class BatteryPassportPageTest extends WebTestCase
{
    use BatteryFixtureTrait;

    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;
    }

    private function openPassportPage(?BatteryProfile $profile, ?string $origin = null): void
    {
        $passport = $this->batteryPassport($this->entityManager, $profile);
        $passport->setOriginMaterials($origin);
        $this->entityManager->persist($passport);
        $this->entityManager->flush();
        $id = $passport->getId();
        $this->entityManager->clear();

        $this->client->request('GET', '/product-passport/' . $id);
    }

    public function testBatteryDataIsDisplayedWithItsUnits(): void
    {
        $this->openPassportPage($this->forkliftProfile());

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test="passport-battery"]');
        self::assertSelectorTextContains('[data-test="passport-battery"] h2', 'Battery');
        self::assertSelectorTextContains('[data-test="passport-battery"]', 'Industrial');
        self::assertSelectorTextContains('[data-test="passport-battery"]', 'Lithium iron phosphate (LFP)');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '600 Ah');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '48 V');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '850 kg');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '65.2 kg CO2e/kWh');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '12 %');
        self::assertSelectorTextContains('[data-test="passport-battery"]', '03/2027');
        self::assertSelectorTextContains('body', 'Version 1');
        self::assertSelectorExists('[data-test="passport-updated-at"]');
    }

    public function testBatteryDefinitionListHasTermsAndDescriptions(): void
    {
        $this->openPassportPage($this->forkliftProfile());

        self::assertSelectorExists('[data-test="passport-battery"] dl dt');
        self::assertSelectorExists('[data-test="passport-battery"] dl dd');
    }

    public function testOptionalFieldsAreNotDisplayedWhenEmpty(): void
    {
        $this->openPassportPage($this->forkliftProfile());

        self::assertSelectorNotExists('[data-test="battery-extinguishing-agent"]');
        self::assertSelectorNotExists('[data-test="battery-warranty"]');
    }

    public function testPassportWithoutBatteryProfileIsUnchanged(): void
    {
        $this->openPassportPage(null, 'France');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-test="passport-battery"]');
        self::assertSelectorTextNotContains('body', 'Nominal capacity');
        self::assertSelectorTextContains('body', 'France');
    }

    public function testPortableBatteryPageShowsTheScopeNotice(): void
    {
        $profile = $this->forkliftProfile();
        $profile->category = BatteryCategory::Portable;

        $this->openPassportPage($profile);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-test="passport-battery-scope-notice"]', 'art. 77(1)');
    }
}
