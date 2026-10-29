<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service;

use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\PassportRevision;

interface PassportRevisionServiceInterface
{
    /**
     * @param DigitalProductPassport $passport
     * @param string $author Email of the author
     */
    public function createRevision(DigitalProductPassport $passport, string $author): void;

    /**
     * @param DigitalProductPassport $passport
     * @return PassportRevision[]
     */
    public function getRevisions(DigitalProductPassport $passport): array;
}
