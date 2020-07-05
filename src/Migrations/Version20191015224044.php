<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191015224044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change abbreviated_statement to a text column, add additional_field indices';
    }

    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_item CHANGE abbreviated_statement abbreviated_statement LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_item CHANGE abbreviated_statement abbreviated_statement LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_561885835E237E06 ON salt_additional_field (name)');
        $this->addSql('CREATE INDEX applies_idx ON salt_additional_field (applies_to)');
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_ls_item CHANGE abbreviated_statement abbreviated_statement VARCHAR(60) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ls_item CHANGE abbreviated_statement abbreviated_statement VARCHAR(60) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('DROP INDEX UNIQ_561885835E237E06 ON salt_additional_field');
        $this->addSql('DROP INDEX applies_idx ON salt_additional_field');
    }
}
