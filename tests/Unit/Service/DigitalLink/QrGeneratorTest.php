<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Unit\Service\DigitalLink;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\QrGenerator;

final class QrGeneratorTest extends TestCase
{
    private const LINK = 'https://id.gs1.org/01/5012345678900';

    /** v5 and v6 both add the margin on each side: 300 + 2 * 10. */
    public function testPngHasPngSignatureAndConfiguredSize(): void
    {
        $png = (new QrGenerator())->generatePng(self::LINK);

        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
        $info = getimagesizefromstring($png);
        self::assertIsArray($info);
        self::assertSame(320, $info[0]);
        self::assertSame(320, $info[1]);
    }

    public function testPngEncodesLinkWithV5Parameters(): void
    {
        $expected = (new PngWriter())->write($this->referenceQrCode(self::LINK, ErrorCorrectionLevel::High))->getString();

        self::assertSame($expected, (new QrGenerator())->generatePng(self::LINK));
    }

    public function testPngUsesHighErrorCorrection(): void
    {
        $low = (new PngWriter())->write($this->referenceQrCode(self::LINK, ErrorCorrectionLevel::Low))->getString();

        self::assertNotSame($low, (new QrGenerator())->generatePng(self::LINK));
    }

    public function testPngEncodesTheGivenLink(): void
    {
        $other = (new PngWriter())->write($this->referenceQrCode(self::LINK . '1', ErrorCorrectionLevel::High))->getString();

        self::assertNotSame($other, (new QrGenerator())->generatePng(self::LINK));
    }

    public function testSvgIsValidSvgDocumentWithConfiguredSize(): void
    {
        $svg = (new QrGenerator())->generateSvg(self::LINK);

        $document = new \DOMDocument();
        self::assertTrue($document->loadXML($svg));
        $root = $document->documentElement;
        self::assertNotNull($root);
        self::assertSame('svg', $root->localName);
        self::assertSame('http://www.w3.org/2000/svg', $root->namespaceURI);
        self::assertSame('320px', $root->getAttribute('width'));
        self::assertSame('320px', $root->getAttribute('height'));
    }

    public function testSvgEncodesLinkWithV5Parameters(): void
    {
        $expected = (new SvgWriter())->write($this->referenceQrCode(self::LINK, ErrorCorrectionLevel::High))->getString();

        self::assertSame($expected, (new QrGenerator())->generateSvg(self::LINK));
    }

    public function testSvgUsesHighErrorCorrection(): void
    {
        $low = (new SvgWriter())->write($this->referenceQrCode(self::LINK, ErrorCorrectionLevel::Low))->getString();

        self::assertNotSame($low, (new QrGenerator())->generateSvg(self::LINK));
    }

    private function referenceQrCode(string $data, ErrorCorrectionLevel $level): QrCode
    {
        return new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $level,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );
    }
}
