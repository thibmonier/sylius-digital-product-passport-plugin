<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Separate from the table creation so that down() drops the foreign key before the referenced table.
 */
final class Version20261112100100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the battery_profile_id column to tmonier_digital_product_passport (US-284).';
    }

    public function up(Schema $schema): void
    {
        $passport = $schema->getTable('tmonier_digital_product_passport');
        $passport->addColumn('battery_profile_id', Types::INTEGER, ['notnull' => false]);
        $passport->addUniqueIndex(['battery_profile_id'], 'UNIQ_23F8D85059337040');
        $passport->addForeignKeyConstraint('tmonier_battery_profile', ['battery_profile_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_23F8D850A7B49D4E');
    }

    public function down(Schema $schema): void
    {
        $passport = $schema->getTable('tmonier_digital_product_passport');
        $passport->removeForeignKey('FK_23F8D850A7B49D4E');
        $passport->dropIndex('UNIQ_23F8D85059337040');
        $passport->dropColumn('battery_profile_id');
    }
}
