<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160824144330 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->addSql("
UPDATE ls_association
   SET `type` = 'Exact Match Of'
 WHERE `type` = 'Exact Match Of Source'
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->addSql("
UPDATE ls_association
   SET `type` = 'Exact Match Of Source'
 WHERE `type` = 'Exact Match Of'
        ");
    }
}
