<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160811235255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_item CHANGE ls_doc_uri ls_doc_uri VARCHAR(300) DEFAULT NULL, CHANGE global_id identifier VARCHAR(300) NOT NULL');
        $this->addSql('ALTER TABLE ls_doc CHANGE global_id identifier VARCHAR(300) NOT NULL');
    }


    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_doc CHANGE identifier global_id VARCHAR(300) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ls_item CHANGE ls_doc_uri ls_doc_uri VARCHAR(300) NOT NULL COLLATE utf8_unicode_ci, CHANGE identifier global_id VARCHAR(300) NOT NULL COLLATE utf8_unicode_ci');
    }
}
