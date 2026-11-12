<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\Battery;

use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;

/**
 * Tells when a battery profile is outside the scope of art. 77(1): informative only, never a refusal.
 */
final readonly class BatteryScopeAdvisor
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function warning(BatteryProfile $profile): ?string
    {
        if ($profile->category === null || $profile->category->isPassportRequired()) {
            return null;
        }

        return $this->translator->trans('tmonier_digital_product_passport.battery.not_mandatory');
    }
}
