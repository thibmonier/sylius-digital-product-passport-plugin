<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink;

/**
 * Validates GTIN (Global Trade Item Number) with check digit verification.
 *
 * Supports GTIN-8, GTIN-12, GTIN-13, and GTIN-14 formats.
 * Uses GS1 check digit algorithm.
 */
final readonly class GtinValidator
{
    /**
     * Validate a GTIN string.
     *
     * @return bool True if the GTIN is valid (correct length and check digit)
     */
    public function isValid(string $gtin): bool
    {
        // Empty GTIN is invalid
        if ($gtin === '') {
            return false;
        }

        // Must contain only digits
        if (!ctype_digit($gtin)) {
            return false;
        }

        // Must be 8, 12, 13, or 14 digits
        $length = strlen($gtin);
        if (!in_array($length, [8, 12, 13, 14], true)) {
            return false;
        }

        // Verify check digit
        return $this->verifyCheckDigit($gtin);
    }

    /**
     * Verify the GTIN check digit using GS1 algorithm.
     *
     * Algorithm:
     * 1. Take digits from right to left (excluding check digit)
     * 2. Multiply by 3 the digits in odd positions (from right), by 1 the digits in even positions
     * 3. Sum all products
     * 4. Check digit = (10 - (sum % 10)) % 10
     */
    private function verifyCheckDigit(string $gtin): bool
    {
        $digits = str_split($gtin);
        $checkDigit = (int) array_pop($digits);

        $sum = 0;
        $position = 1;

        // Process digits from right to left
        for ($i = count($digits) - 1; $i >= 0; --$i) {
            $digit = (int) $digits[$i];
            $multiplier = ($position % 2 === 1) ? 3 : 1;
            $sum += $digit * $multiplier;
            ++$position;
        }

        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === $calculatedCheckDigit;
    }
}
