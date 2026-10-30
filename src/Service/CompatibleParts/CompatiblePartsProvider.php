<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\CompatibleParts;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Tmonier\SyliusCustomerAssetManagementPlugin\Repository\EquipmentModelRepositoryInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

final class CompatiblePartsProvider
{
    public function __construct(
        private readonly ?EquipmentModelRepositoryInterface $equipmentModelRepository = null,
    ) {
    }

    /**
     * Get compatible product variants for a passport's equipment model.
     *
     * @return list<ProductVariantInterface>
     */
    public function getCompatibleParts(DigitalProductPassport $passport): array
    {
        $modelCode = $passport->getEquipmentModelCode();

        if ($modelCode === null || $this->equipmentModelRepository === null) {
            return [];
        }

        $model = $this->equipmentModelRepository->findOneByCode($modelCode);

        if ($model === null) {
            return [];
        }

        return array_values($model->getCompatibleVariants()->toArray());
    }
}
