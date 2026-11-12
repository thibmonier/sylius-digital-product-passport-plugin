<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the tmonier_battery_profile table and the battery_profile_id column of passports (US-284).';
    }

    public function up(Schema $schema): void
    {
        $profile = $schema->createTable('tmonier_battery_profile');
        $profile->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $profile->addColumn('category', Types::STRING, ['length' => 32]);
        $profile->addColumn('chemistry', Types::STRING, ['length' => 64]);
        $profile->addColumn('nominal_capacity_ah', Types::FLOAT);
        $profile->addColumn('nominal_voltage_v', Types::FLOAT);
        $profile->addColumn('weight_kg', Types::FLOAT);
        $profile->addColumn('manufactured_on', Types::DATE_IMMUTABLE);
        $profile->addColumn('min_voltage_v', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('max_voltage_v', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('manufacturing_place', Types::STRING, ['length' => 255, 'notnull' => false]);
        $profile->addColumn('carbon_footprint_kg_co2e_per_kwh', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('recycled_cobalt_percent', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('recycled_lithium_percent', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('recycled_nickel_percent', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('recycled_lead_percent', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('renewable_share_percent', Types::FLOAT, ['notnull' => false]);
        $profile->addColumn('cycle_life', Types::INTEGER, ['notnull' => false]);
        $profile->addColumn('hazardous_substances', Types::TEXT, ['notnull' => false]);
        $profile->addColumn('critical_raw_materials', Types::TEXT, ['notnull' => false]);
        $profile->addColumn('extinguishing_agent', Types::STRING, ['length' => 255, 'notnull' => false]);
        $profile->addColumn('warranty_months', Types::INTEGER, ['notnull' => false]);
        $profile->setPrimaryKey(['id']);

        $passport = $schema->getTable('tmonier_digital_product_passport');
        $passport->addColumn('battery_profile_id', Types::INTEGER, ['notnull' => false]);
        $passport->addUniqueIndex(['battery_profile_id'], 'UNIQ_TMONIER_DPP_BATTERY_PROFILE');
        $passport->addForeignKeyConstraint('tmonier_battery_profile', ['battery_profile_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_TMONIER_DPP_BATTERY_PROFILE');
    }

    public function down(Schema $schema): void
    {
        $passport = $schema->getTable('tmonier_digital_product_passport');
        $passport->removeForeignKey('FK_TMONIER_DPP_BATTERY_PROFILE');
        $passport->dropIndex('UNIQ_TMONIER_DPP_BATTERY_PROFILE');
        $passport->dropColumn('battery_profile_id');

        $schema->dropTable('tmonier_battery_profile');
    }
}
