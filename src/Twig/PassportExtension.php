<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\DigitalLinkGenerator;
use Tmonier\SyliusDigitalProductPassportPlugin\Service\DigitalLink\QrGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PassportExtension extends AbstractExtension
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DigitalLinkGenerator $digitalLinkGenerator,
        private readonly QrGenerator $qrGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_product_passport', $this->getProductPassport(...)),
            new TwigFunction('generate_gs1_digital_link', $this->generateGs1DigitalLink(...)),
            new TwigFunction('generate_qr_svg', $this->generateQrSvg(...), ['is_safe' => ['html']]),
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

    public function generateGs1DigitalLink(string $gtin, ?string $lot = null): ?string
    {
        try {
            return $this->digitalLinkGenerator->generate($gtin, $lot);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function generateQrSvg(string $url): ?string
    {
        try {
            return $this->qrGenerator->generateSvg($url);
        } catch (\Exception) {
            return null;
        }
    }
}
