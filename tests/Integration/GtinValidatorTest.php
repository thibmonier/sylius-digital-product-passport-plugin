<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\GtinValidator;

/**
 * Tests for GTIN validation (US-232 T-232-02).
 * Gherkin scenario: "GTIN absent ou invalide (erreur)"
 *
 * This test MUST fail first (GtinValidator not created yet).
 */
final class GtinValidatorTest extends KernelTestCase
{
    public function testValidGtin13ReturnsTrue(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        // GTIN-13 with valid check digit: 5012345678900
        self::assertTrue($validator->isValid('5012345678900'));
    }

    public function testValidGtin14ReturnsTrue(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        // GTIN-14 with valid check digit: 15012345678907
        self::assertTrue($validator->isValid('15012345678907'));
    }

    public function testInvalidCheckDigitReturnsFalse(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        // GTIN with invalid check digit (should be 0, not 9)
        self::assertFalse($validator->isValid('5012345678909'));
    }

    public function testEmptyGtinReturnsFalse(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        self::assertFalse($validator->isValid(''));
    }

    public function testNonNumericGtinReturnsFalse(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        self::assertFalse($validator->isValid('abc123'));
    }

    public function testInvalidLengthReturnsFalse(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var GtinValidator $validator */
        $validator = $container->get(GtinValidator::class);

        // GTIN must be 8, 12, 13, or 14 digits
        self::assertFalse($validator->isValid('123456'));
    }
}
