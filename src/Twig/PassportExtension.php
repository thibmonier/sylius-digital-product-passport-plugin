<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PassportExtension extends AbstractExtension
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_product_passport', $this->getProductPassport(...)),
        ];
    }

    public function getProductPassport(ProductInterface $product): ?DigitalProductPassport
    {
        $variants = $product->getVariants();

        if ($variants->isEmpty()) {
            return null;
        }

        $firstVariant = $variants->first();

        return $this->entityManager
            ->getRepository(DigitalProductPassport::class)
            ->findOneBy(['productVariant' => $firstVariant]);
    }
}
