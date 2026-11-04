<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

/**
 * Generates QR codes (SVG and PNG) for GS1 Digital Links.
 *
 * QR codes use black on white (21:1 contrast ratio, AAA compliant).
 */
final class QrGenerator
{
    private const SIZE = 300;

    private const MARGIN = 10;

    /**
     * Generate a QR code as SVG.
     *
     * @param string $link The GS1 Digital Link URI to encode
     *
     * @return string SVG markup
     */
    public function generateSvg(string $link): string
    {
        return $this->generate(new SvgWriter(), $link);
    }

    /**
     * Generate a QR code as PNG.
     *
     * @param string $link The GS1 Digital Link URI to encode
     *
     * @return string PNG binary data
     */
    public function generatePng(string $link): string
    {
        return $this->generate(new PngWriter(), $link);
    }

    /**
     * Built from QrCode and a writer rather than Builder: the Builder API differs
     * between endroid/qr-code 5 (fluent, create()) and 6 (constructor), QrCode does not.
     */
    private function generate(WriterInterface $writer, string $link): string
    {
        $qrCode = new QrCode(
            data: $link,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: self::SIZE,
            margin: self::MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $writer->write($qrCode)->getString();
    }
}
