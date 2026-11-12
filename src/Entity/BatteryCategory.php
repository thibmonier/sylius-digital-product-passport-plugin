<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Entity;

/**
 * Battery categories of Regulation (EU) 2023/1542.
 */
enum BatteryCategory: string
{
    case Lmt = 'lmt';
    case Industrial = 'industrial';
    case ElectricVehicle = 'electric_vehicle';
    case Portable = 'portable';
    case Sli = 'sli';

    /**
     * Art. 77(1): the battery passport is required for LMT, industrial and electric vehicle batteries only.
     * The 2 kWh threshold for industrial batteries is not modelled: the plugin does not store the energy.
     */
    public function isPassportRequired(): bool
    {
        return match ($this) {
            self::Lmt, self::Industrial, self::ElectricVehicle => true,
            self::Portable, self::Sli => false,
        };
    }
}
