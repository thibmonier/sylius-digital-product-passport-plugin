<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusDigitalProductPassportPlugin\Support;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryCategory;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

trait BatteryFixtureTrait
{
    private function createVariant(EntityManagerInterface $entityManager): ProductVariant
    {
        /** @var ChannelRepositoryInterface<ChannelInterface> $channelRepository */
        $channelRepository = self::getContainer()->get('sylius.repository.channel');
        $channel = $channelRepository->findOneBy([]);
        self::assertNotNull($channel);

        $product = new Product();
        $product->setCode('battery-product-' . uniqid());
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName('Forklift battery');
        $product->setSlug('battery-product-' . uniqid());
        $product->setEnabled(true);
        $product->addChannel($channel);

        $variant = new ProductVariant();
        $variant->setCode('battery-variant-' . uniqid());
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName('Forklift battery 48 V');
        $variant->setProduct($product);

        $pricing = new ChannelPricing();
        $pricing->setChannelCode((string) $channel->getCode());
        $pricing->setPrice(1000);
        $variant->addChannelPricing($pricing);

        $entityManager->persist($product);
        $entityManager->persist($variant);
        $entityManager->flush();

        return $variant;
    }

    /**
     * A forklift traction battery: industrial category, only the mandatory fields plus a few optional ones.
     */
    private function forkliftProfile(): BatteryProfile
    {
        $profile = new BatteryProfile();
        $profile->category = BatteryCategory::Industrial;
        $profile->chemistry = 'Lithium iron phosphate (LFP)';
        $profile->nominalCapacityAh = 600.0;
        $profile->nominalVoltageV = 48.0;
        $profile->weightKg = 850.0;
        $profile->manufacturedOn = new \DateTimeImmutable('2027-03-01');
        $profile->manufacturingPlace = 'Nantes, France';
        $profile->carbonFootprintKgCo2ePerKwh = 65.2;
        $profile->recycledCobaltPercent = 12.0;

        return $profile;
    }

    private function batteryPassport(EntityManagerInterface $entityManager, ?BatteryProfile $profile): DigitalProductPassport
    {
        $passport = new DigitalProductPassport();
        $passport->setProductVariant($this->createVariant($entityManager));
        $passport->setBatteryProfile($profile);

        return $passport;
    }
}
