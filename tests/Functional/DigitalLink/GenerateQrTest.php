<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\DigitalLink;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\QrGenerator;

/**
 * Tests for QR code generation (US-232 T-232-02).
 * Gherkin scenarios: "Téléchargement (alternatif)" + "Scan du lien (erreur évitée)"
 *
 * This test MUST fail first (QrGenerator not created yet).
 */
final class GenerateQrTest extends KernelTestCase
{
    public function testGenerateQrSvgReturnsValidSvg(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var QrGenerator $generator */
        $generator = $container->get(QrGenerator::class);

        $link = 'https://id.gs1.org/01/5012345678900';
        $svg = $generator->generateSvg($link);

        self::assertStringContainsString('<svg', $svg);
        self::assertStringContainsString('</svg>', $svg);
        self::assertStringContainsString('xmlns=', $svg);
        self::assertStringContainsString('<path', $svg); // QR code patterns
    }

    public function testGenerateQrPngReturnsValidPng(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var QrGenerator $generator */
        $generator = $container->get(QrGenerator::class);

        $link = 'https://id.gs1.org/01/5012345678900';
        $png = $generator->generatePng($link);

        // PNG magic bytes
        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
    }

    public function testQrCodeHasSufficientContrast(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var QrGenerator $generator */
        $generator = $container->get(QrGenerator::class);

        $link = 'https://id.gs1.org/01/5012345678900';
        $svg = $generator->generateSvg($link);

        // QR code must have black (#000000 or equivalent) on white background
        // for AAA contrast (21:1 ratio)
        self::assertStringContainsString('#000', $svg);
    }
}
