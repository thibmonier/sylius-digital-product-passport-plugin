<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tmonier_passport_revision')]
class PassportRevision
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DigitalProductPassport::class)]
    #[ORM\JoinColumn(name: 'passport_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private DigitalProductPassport $passport;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $author;

    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $data = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassport(): DigitalProductPassport
    {
        return $this->passport;
    }

    public function setPassport(DigitalProductPassport $passport): void
    {
        $this->passport = $passport;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
