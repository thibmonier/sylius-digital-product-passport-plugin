<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Public data of a battery passport (Regulation (EU) 2023/1542, art. 77 and annex XIII point 1).
 *
 * Plain data holder with public properties: Doctrine, the validator and Twig read them directly.
 * The manufacturing date is stored as the first day of the manufacturing month.
 */
#[ORM\Entity]
#[ORM\Table(name: 'tmonier_battery_profile')]
class BatteryProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null; // @phpstan-ignore property.unusedType (assigned by Doctrine)

    #[ORM\Column(name: 'category', type: Types::STRING, length: 32, enumType: BatteryCategory::class)]
    public ?BatteryCategory $category = null;

    #[ORM\Column(name: 'chemistry', type: Types::STRING, length: 64)]
    public ?string $chemistry = null;

    #[ORM\Column(name: 'nominal_capacity_ah', type: Types::FLOAT)]
    public ?float $nominalCapacityAh = null;

    #[ORM\Column(name: 'nominal_voltage_v', type: Types::FLOAT)]
    public ?float $nominalVoltageV = null;

    #[ORM\Column(name: 'weight_kg', type: Types::FLOAT)]
    public ?float $weightKg = null;

    #[ORM\Column(name: 'manufactured_on', type: Types::DATE_IMMUTABLE)]
    public ?\DateTimeImmutable $manufacturedOn = null;

    #[ORM\Column(name: 'min_voltage_v', type: Types::FLOAT, nullable: true)]
    public ?float $minVoltageV = null;

    #[ORM\Column(name: 'max_voltage_v', type: Types::FLOAT, nullable: true)]
    public ?float $maxVoltageV = null;

    #[ORM\Column(name: 'manufacturing_place', type: Types::STRING, length: 255, nullable: true)]
    public ?string $manufacturingPlace = null;

    #[ORM\Column(name: 'carbon_footprint_kg_co2e_per_kwh', type: Types::FLOAT, nullable: true)]
    public ?float $carbonFootprintKgCo2ePerKwh = null;

    #[ORM\Column(name: 'recycled_cobalt_percent', type: Types::FLOAT, nullable: true)]
    public ?float $recycledCobaltPercent = null;

    #[ORM\Column(name: 'recycled_lithium_percent', type: Types::FLOAT, nullable: true)]
    public ?float $recycledLithiumPercent = null;

    #[ORM\Column(name: 'recycled_nickel_percent', type: Types::FLOAT, nullable: true)]
    public ?float $recycledNickelPercent = null;

    #[ORM\Column(name: 'recycled_lead_percent', type: Types::FLOAT, nullable: true)]
    public ?float $recycledLeadPercent = null;

    #[ORM\Column(name: 'renewable_share_percent', type: Types::FLOAT, nullable: true)]
    public ?float $renewableSharePercent = null;

    #[ORM\Column(name: 'cycle_life', type: Types::INTEGER, nullable: true)]
    public ?int $cycleLife = null;

    #[ORM\Column(name: 'hazardous_substances', type: Types::TEXT, nullable: true)]
    public ?string $hazardousSubstances = null;

    #[ORM\Column(name: 'critical_raw_materials', type: Types::TEXT, nullable: true)]
    public ?string $criticalRawMaterials = null;

    #[ORM\Column(name: 'extinguishing_agent', type: Types::STRING, length: 255, nullable: true)]
    public ?string $extinguishingAgent = null;

    #[ORM\Column(name: 'warranty_months', type: Types::INTEGER, nullable: true)]
    public ?int $warrantyMonths = null;
}
