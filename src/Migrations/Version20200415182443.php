<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200415182443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow definition object titles to be longer (max 1024 chars)';
    }

    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_def_association_grouping CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_association_grouping CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE ls_def_concept CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_concept CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE ls_def_grade CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_grade CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE ls_def_item_type CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_item_type CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE ls_def_licence CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_licence CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE ls_def_subject CHANGE title title VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE audit_ls_def_subject CHANGE title title VARCHAR(1024) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_ls_def_association_grouping CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE audit_ls_def_concept CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE audit_ls_def_grade CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE audit_ls_def_item_type CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE audit_ls_def_licence CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE audit_ls_def_subject CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_association_grouping CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_concept CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_grade CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_item_type CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_licence CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ls_def_subject CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
