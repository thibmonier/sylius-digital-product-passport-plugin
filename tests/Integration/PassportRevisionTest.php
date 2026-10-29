<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\PassportRevisionServiceInterface;

/**
 * Tests for passport revision service (US-230 T-230-04).
 * Gherkin scenario: "Révision (alternatif)"
 *
 * These tests MUST fail first (service not created yet).
 */
final class PassportRevisionTest extends KernelTestCase
{
    public function testRevisionIsCreatedWhenPassportIsModified(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var PassportRevisionServiceInterface $revisionService */
        $revisionService = $container->get('tmonier_digital_product_passport.revision_service');

        $passport = new DigitalProductPassport();
        $passport->setOriginMaterials('France');

        // Create first revision
        $revisionService->createRevision($passport, 'author@example.com');

        // Modify passport
        $passport->setOriginMaterials('Germany');

        // Create second revision
        $revisionService->createRevision($passport, 'author@example.com');

        $revisions = $revisionService->getRevisions($passport);

        self::assertCount(2, $revisions);
    }

    public function testRevisionContainsDateAndAuthor(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var PassportRevisionServiceInterface $revisionService */
        $revisionService = $container->get('tmonier_digital_product_passport.revision_service');

        $passport = new DigitalProductPassport();
        $passport->setOriginMaterials('France');

        $revisionService->createRevision($passport, 'alice@example.com');

        $revisions = $revisionService->getRevisions($passport);
        $revision = $revisions[0];

        self::assertSame('alice@example.com', $revision->getAuthor());
        self::assertInstanceOf(\DateTimeImmutable::class, $revision->getCreatedAt());
    }
}
