<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink;

/**
 * Generates GS1 Digital Link URIs.
 *
 * Format: https://id.gs1.org/01/{GTIN}[/10/{LOT}]
 *
 * @see https://www.gs1.org/standards/gs1-digital-link
 */
final readonly class DigitalLinkGenerator
{
    public function __construct(
        private GtinValidator $gtinValidator,
    ) {
    }

    /**
     * Generate a GS1 Digital Link URI.
     *
     * @param string $gtin GTIN-8, GTIN-12, GTIN-13, or GTIN-14
     * @param string|null $lot Optional lot number
     *
     * @throws \InvalidArgumentException if GTIN is invalid (clé de contrôle incorrecte)
     */
    public function generate(string $gtin, ?string $lot = null): string
    {
        if (!$this->gtinValidator->isValid($gtin)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'GTIN invalide : clé de contrôle incorrecte pour "%s"',
                    $gtin,
                ),
            );
        }

        $link = sprintf('https://id.gs1.org/01/%s', $gtin);

        if ($lot !== null && $lot !== '') {
            $link .= sprintf('/10/%s', rawurlencode($lot));
        }

        return $link;
    }
}
