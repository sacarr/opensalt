<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191007212215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed unused group name and uri fields';
    }

    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_association DROP group_name, DROP group_uri');
        $this->addSql('ALTER TABLE audit_ls_association DROP group_name, DROP group_uri');
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_ls_association ADD group_name VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD group_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ls_association ADD group_name VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD group_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
