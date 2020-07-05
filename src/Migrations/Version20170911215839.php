<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170911215839 extends AbstractMigration
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

        $this->addSql("SELECT 'Merged into previous migration'");
        // Merged into previous migration
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

        $this->addSql("SELECT 'Merged into previous migration'");
        // Merged into previous migration
    }
}
