<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tmonier_digital_product_passport table (tmonier/sylius-digital-product-passport-plugin).';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('tmonier_digital_product_passport');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('product_variant_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('product_identifier', Types::STRING, ['length' => 14, 'notnull' => false, 'default' => null]);
        $table->addColumn('digital_link_uri', Types::STRING, ['length' => 255, 'notnull' => false, 'default' => null]);
        $table->addColumn('origin_materials', Types::TEXT, ['notnull' => false, 'default' => null]);
        $table->addColumn('recyclability', Types::TEXT, ['notnull' => false, 'default' => null]);
        $table->addColumn('repairability', Types::TEXT, ['notnull' => false, 'default' => null]);
        $table->addColumn('version', Types::INTEGER, ['notnull' => true, 'default' => 1]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE, ['notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['product_variant_id'], 'UNIQ_dpp_product_variant');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('tmonier_digital_product_passport');
    }
}
