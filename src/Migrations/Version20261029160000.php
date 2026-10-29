<?php

declare(strict_types=1);

namespace Tmonier\SyliusDigitalProductPassportPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tmonier_passport_revision table (tmonier/sylius-digital-product-passport-plugin).';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('tmonier_passport_revision');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('passport_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('author', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('data', Types::JSON, ['notnull' => true]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['passport_id']);
        $table->addForeignKeyConstraint(
            'tmonier_digital_product_passport',
            ['passport_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('tmonier_passport_revision');
    }
}
