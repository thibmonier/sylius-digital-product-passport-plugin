<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration\Battery;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Tmonier\SyliusDigitalProductPassportPlugin\Support\BatteryFixtureTrait;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryCategory;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\Battery\BatteryScopeAdvisor;

/**
 * US-284: validation of the battery profile (missing mandatory field, out-of-range value, scope warning).
 */
final class BatteryProfileValidationTest extends KernelTestCase
{
    use BatteryFixtureTrait;

    private function validate(BatteryProfile $profile): ConstraintViolationListInterface
    {
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');

        return $validator->validate($this->batteryPassport($entityManager, $profile));
    }

    private function messagesOf(ConstraintViolationListInterface $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        return implode("\n", $messages);
    }

    public function testCompleteProfileIsValid(): void
    {
        $violations = $this->validate($this->forkliftProfile());

        self::assertCount(0, $violations, $this->messagesOf($violations));
    }

    public function testMissingNominalCapacityIsRefusedAndNamed(): void
    {
        $profile = $this->forkliftProfile();
        $profile->nominalCapacityAh = null;

        $violations = $this->validate($profile);

        self::assertCount(1, $violations);
        self::assertSame('batteryProfile.nominalCapacityAh', $violations->get(0)->getPropertyPath());
        self::assertStringContainsString('capacity', (string) $violations->get(0)->getMessage());
    }

    public function testRecycledShareOf120PercentIsRefused(): void
    {
        $profile = $this->forkliftProfile();
        $profile->recycledCobaltPercent = 120.0;

        $violations = $this->validate($profile);

        self::assertCount(1, $violations);
        self::assertSame('batteryProfile.recycledCobaltPercent', $violations->get(0)->getPropertyPath());
    }

    public function testNegativeWeightIsRefused(): void
    {
        $profile = $this->forkliftProfile();
        $profile->weightKg = -1.0;

        $violations = $this->validate($profile);

        self::assertCount(1, $violations);
        self::assertSame('batteryProfile.weightKg', $violations->get(0)->getPropertyPath());
    }

    #[DataProvider('outOfScopeCategories')]
    public function testPortableAndSliBatteriesAreAcceptedWithAWarning(BatteryCategory $category): void
    {
        $profile = $this->forkliftProfile();
        $profile->category = $category;

        $violations = $this->validate($profile);
        /** @var BatteryScopeAdvisor $advisor */
        $advisor = self::getContainer()->get(BatteryScopeAdvisor::class);

        self::assertCount(0, $violations, $this->messagesOf($violations));
        self::assertNotNull($advisor->warning($profile));
        self::assertStringContainsString('art. 77(1)', (string) $advisor->warning($profile));
    }

    /**
     * @return iterable<string, array{BatteryCategory}>
     */
    public static function outOfScopeCategories(): iterable
    {
        yield 'portable' => [BatteryCategory::Portable];
        yield 'sli' => [BatteryCategory::Sli];
    }

    public function testIndustrialBatteryHasNoWarning(): void
    {
        self::bootKernel();
        /** @var BatteryScopeAdvisor $advisor */
        $advisor = self::getContainer()->get(BatteryScopeAdvisor::class);

        self::assertNull($advisor->warning($this->forkliftProfile()));
    }
}
