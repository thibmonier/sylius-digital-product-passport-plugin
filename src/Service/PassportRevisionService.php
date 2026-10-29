<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\PassportRevision;

final readonly class PassportRevisionService implements PassportRevisionServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createRevision(DigitalProductPassport $passport, string $author): void
    {
        // Ensure passport is persisted
        if ($passport->getId() === null) {
            $this->entityManager->persist($passport);
            $this->entityManager->flush();
        }

        $revision = new PassportRevision();
        $revision->setPassport($passport);
        $revision->setAuthor($author);
        $revision->setData([
            'product_identifier' => $passport->getProductIdentifier(),
            'digital_link_uri' => $passport->getDigitalLinkUri(),
            'origin_materials' => $passport->getOriginMaterials(),
            'recyclability' => $passport->getRecyclability(),
            'repairability' => $passport->getRepairability(),
            'version' => $passport->getVersion(),
        ]);

        $this->entityManager->persist($revision);
        $this->entityManager->flush();
    }

    public function getRevisions(DigitalProductPassport $passport): array
    {
        return $this->entityManager
            ->getRepository(PassportRevision::class)
            ->findBy(['passport' => $passport], ['createdAt' => 'ASC']);
    }
}
