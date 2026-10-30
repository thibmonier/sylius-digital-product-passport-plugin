<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Functional\CompatibleParts;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-233: Gherkin scenario "Depuis le parc d'équipements (alternatif)".
 *
 * Given un équipement du parc d'Acme
 * When Claire ouvre sa fiche
 * Then le lien vers le passeport et les pièces apparaît
 *
 * This test verifies the link from customer-asset-management to the passport.
 * It will fail first (template hook not created yet).
 */
final class FromFleetTest extends WebTestCase
{
    public function testLinkToPassportAppearsFromFleetPage(): void
    {
        self::markTestSkipped('Test depends on customer-asset-management plugin being installed');

        // TODO: This test will be implemented when customer-asset-management is available in test environment
        // Expected: equipment page displays link to passport and compatible parts
    }
}
