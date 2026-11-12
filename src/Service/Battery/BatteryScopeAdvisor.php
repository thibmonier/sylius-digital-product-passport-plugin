<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Service\Battery;

use Tmonier\SyliusDigitalProductPassportPlugin\Entity\BatteryProfile;

final class BatteryScopeAdvisor
{
    public function warning(BatteryProfile $profile): ?string
    {
        return null;
    }
}
