<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191004200339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add text field for item_type, remove licence_uri';
    }

    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_item ADD item_type_text VARCHAR(255) DEFAULT NULL AFTER item_type_id, DROP licence_uri');
        $this->addSql('ALTER TABLE audit_ls_item ADD item_type_text VARCHAR(255) DEFAULT NULL AFTER item_type_id, DROP licence_uri');
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_ls_item ADD licence_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP item_type_text');
        $this->addSql('ALTER TABLE ls_item ADD licence_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP item_type_text');
    }
}
