<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\CompatibleParts\CompatiblePartsProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PassportExtension extends AbstractExtension
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompatiblePartsProvider $compatiblePartsProvider,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_product_passport', $this->getProductPassport(...)),
            new TwigFunction('passport_find_by_equipment_model_code', $this->findPassportByEquipmentModelCode(...)),
            new TwigFunction('passport_get_compatible_parts', $this->getCompatibleParts(...)),
        ];
    }

    public function findPassportByEquipmentModelCode(string $code): ?DigitalProductPassport
    {
        $passport = $this->entityManager
            ->getRepository(DigitalProductPassport::class)
            ->findOneBy(['equipmentModelCode' => $code, 'published' => true]);

        return $passport instanceof DigitalProductPassport ? $passport : null;
    }

    /**
     * @return list<\Sylius\Component\Core\Model\ProductVariantInterface>
     */
    public function getCompatibleParts(DigitalProductPassport $passport): array
    {
        return $this->compatiblePartsProvider->getCompatibleParts($passport);
    }

    public function getProductPassport(ProductInterface $product): ?DigitalProductPassport
    {
        $variants = $product->getVariants();
        if ($variants->isEmpty()) {
            return null;
        }

        $variant = $variants->first();
        if ($variant === false) {
            return null;
        }

        $passport = $this->entityManager
            ->getRepository(DigitalProductPassport::class)
            ->findOneBy(['productVariant' => $variant]);

        return $passport instanceof DigitalProductPassport ? $passport : null;
    }
}
