<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariantInterface;

#[ORM\Entity]
#[ORM\Table(name: 'tmonier_digital_product_passport')]
class DigitalProductPassport
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null; // @phpstan-ignore property.unusedType (assigned by Doctrine)

    #[ORM\OneToOne(targetEntity: ProductVariantInterface::class)]
    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ProductVariantInterface $productVariant;

    #[ORM\Column(name: 'product_identifier', type: Types::STRING, length: 14, nullable: true)]
    private ?string $productIdentifier = null;

    #[ORM\Column(name: 'digital_link_uri', type: Types::STRING, length: 255, nullable: true)]
    private ?string $digitalLinkUri = null;

    #[ORM\Column(name: 'origin_materials', type: Types::TEXT, nullable: true)]
    private ?string $originMaterials = null;

    #[ORM\Column(name: 'recyclability', type: Types::TEXT, nullable: true)]
    private ?string $recyclability = null;

    #[ORM\Column(name: 'repairability', type: Types::TEXT, nullable: true)]
    private ?string $repairability = null;

    #[ORM\Column(name: 'version', type: Types::INTEGER)]
    private int $version = 1;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    public function getProductIdentifier(): ?string
    {
        return $this->productIdentifier;
    }

    public function setProductIdentifier(?string $productIdentifier): void
    {
        $this->productIdentifier = $productIdentifier;
    }

    public function getDigitalLinkUri(): ?string
    {
        return $this->digitalLinkUri;
    }

    public function setDigitalLinkUri(?string $digitalLinkUri): void
    {
        $this->digitalLinkUri = $digitalLinkUri;
    }

    public function getOriginMaterials(): ?string
    {
        return $this->originMaterials;
    }

    public function setOriginMaterials(?string $originMaterials): void
    {
        $this->originMaterials = $originMaterials;
    }

    public function getRecyclability(): ?string
    {
        return $this->recyclability;
    }

    public function setRecyclability(?string $recyclability): void
    {
        $this->recyclability = $recyclability;
    }

    public function getRepairability(): ?string
    {
        return $this->repairability;
    }

    public function setRepairability(?string $repairability): void
    {
        $this->repairability = $repairability;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Export passport to JSON with versioned schema.
     *
     * Schema version 1 format:
     * {
     *   "schemaVersion": 1,
     *   "productIdentifier": "string|null",
     *   "digitalLinkUri": "string|null",
     *   "originMaterials": "string|null",
     *   "recyclability": "string|null",
     *   "repairability": "string|null",
     *   "version": integer,
     *   "createdAt": "ISO 8601",
     *   "updatedAt": "ISO 8601"
     * }
     */
    public function toJson(): string
    {
        $data = [
            'schemaVersion' => 1,
            'productIdentifier' => $this->productIdentifier,
            'digitalLinkUri' => $this->digitalLinkUri,
            'originMaterials' => $this->originMaterials,
            'recyclability' => $this->recyclability,
            'repairability' => $this->repairability,
            'version' => $this->version,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];

        return json_encode($data, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE);
    }
}
