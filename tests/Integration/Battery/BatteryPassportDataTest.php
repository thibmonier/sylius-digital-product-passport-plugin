<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration\Battery;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Tmonier\SyliusDigitalProductPassportPlugin\Support\BatteryFixtureTrait;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\PassportRevision;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\PassportRevisionServiceInterface;

/**
 * US-284: JSON export, revision history and translations of the battery profile.
 */
final class BatteryPassportDataTest extends KernelTestCase
{
    use BatteryFixtureTrait;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;
    }

    private function capacityOf(PassportRevision $revision): mixed
    {
        $battery = $revision->getData()['battery'] ?? null;

        return \is_array($battery) ? ($battery['nominalCapacityAh'] ?? null) : null;
    }

    public function testJsonOfPassportWithBatteryProfileIsSchemaVersion2WithABatteryBlock(): void
    {
        $passport = $this->batteryPassport($this->entityManager, $this->forkliftProfile());

        /** @var array<string, mixed> $data */
        $data = json_decode($passport->toJson(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame(2, $data['schemaVersion']);
        self::assertIsArray($data['battery']);
        self::assertSame('industrial', $data['battery']['category']);
        self::assertSame('Lithium iron phosphate (LFP)', $data['battery']['chemistry']);
        self::assertSame(600.0, $data['battery']['nominalCapacityAh']);
        self::assertSame('2027-03', $data['battery']['manufacturedOn']);
    }

    public function testJsonOfPassportWithoutBatteryProfileKeepsTheVersion1Keys(): void
    {
        $passport = $this->batteryPassport($this->entityManager, null);

        /** @var array<string, mixed> $data */
        $data = json_decode($passport->toJson(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame(1, $data['schemaVersion']);
        self::assertSame([
            'schemaVersion',
            'productIdentifier',
            'digitalLinkUri',
            'originMaterials',
            'recyclability',
            'repairability',
            'version',
            'createdAt',
            'updatedAt',
        ], array_keys($data));
    }

    public function testRevisionRecordsTheBatteryProfileAndItsModifications(): void
    {
        /** @var PassportRevisionServiceInterface $revisions */
        $revisions = self::getContainer()->get('tmonier_digital_product_passport.revision_service');
        $profile = $this->forkliftProfile();
        $passport = $this->batteryPassport($this->entityManager, $profile);

        $revisions->createRevision($passport, 'alice@example.com');
        $profile->nominalCapacityAh = 700.0;
        $revisions->createRevision($passport, 'alice@example.com');

        $history = $revisions->getRevisions($passport);

        self::assertCount(2, $history);
        self::assertArrayHasKey('battery', $history[0]->getData());
        self::assertSame(600.0, $this->capacityOf($history[0]));
        self::assertSame(700.0, $this->capacityOf($history[1]));
    }

    public function testRevisionOfPassportWithoutBatteryProfileKeepsItsShape(): void
    {
        /** @var PassportRevisionServiceInterface $revisions */
        $revisions = self::getContainer()->get('tmonier_digital_product_passport.revision_service');
        $passport = $this->batteryPassport($this->entityManager, null);

        $revisions->createRevision($passport, 'alice@example.com');

        self::assertArrayNotHasKey('battery', $revisions->getRevisions($passport)[0]->getData());
    }

    public function testBatteryLabelsAreTranslatedInFrenchAndEnglish(): void
    {
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get('translator');
        $key = 'tmonier_digital_product_passport.battery.nominal_capacity';

        self::assertSame('Nominal capacity', $translator->trans($key, [], 'messages', 'en'));
        self::assertSame('Capacité nominale', $translator->trans($key, [], 'messages', 'fr'));
    }
}
