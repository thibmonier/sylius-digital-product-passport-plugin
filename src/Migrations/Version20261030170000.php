<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261030170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add equipment_model_code column to tmonier_digital_product_passport table (US-233).';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('tmonier_digital_product_passport');
        $table->addColumn('equipment_model_code', Types::STRING, ['length' => 64, 'notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('tmonier_digital_product_passport');
        $table->dropColumn('equipment_model_code');
    }
}
