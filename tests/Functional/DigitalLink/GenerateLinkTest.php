<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\DigitalLink;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\DigitalLinkGenerator;

/**
 * Tests for GS1 Digital Link generation (US-232 T-232-02).
 * Gherkin scenarios: "QR généré (nominal)" + "Lien avec lot (alternatif)"
 *
 * This test MUST fail first (DigitalLinkGenerator not created yet).
 */
final class GenerateLinkTest extends KernelTestCase
{
    public function testGenerateLinkWithGtinOnly(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var DigitalLinkGenerator $generator */
        $generator = $container->get(DigitalLinkGenerator::class);

        $gtin = '5012345678900';
        $link = $generator->generate($gtin);

        self::assertStringContainsString('id.gs1.org', $link);
        self::assertStringContainsString('/01/', $link);
        self::assertStringContainsString($gtin, $link);
    }

    public function testGenerateLinkWithGtinAndLot(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var DigitalLinkGenerator $generator */
        $generator = $container->get(DigitalLinkGenerator::class);

        $gtin = '5012345678900';
        $lot = 'LOT2026001';
        $link = $generator->generate($gtin, $lot);

        self::assertStringContainsString('id.gs1.org', $link);
        self::assertStringContainsString('/01/', $link);
        self::assertStringContainsString($gtin, $link);
        self::assertStringContainsString('/10/', $link);
        self::assertStringContainsString($lot, $link);
    }

    public function testGenerateLinkWithInvalidGtinThrowsException(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var DigitalLinkGenerator $generator */
        $generator = $container->get(DigitalLinkGenerator::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('clé de contrôle');

        $generator->generate('5012345678909'); // Invalid check digit
    }
}
