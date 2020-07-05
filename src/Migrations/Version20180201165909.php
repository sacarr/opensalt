<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;

class Version20180201165909 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
      if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
        $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
        return;
    }

        $sql = <<<'xENDx'
INSERT IGNORE INTO ls_def_licence
  (identifier, uri, title, licence_text, description, updated_at)
SELECT *
  FROM (SELECT :uuid, :uri, :title, :licence_text, :description, NOW()) AS tmp
 WHERE NOT EXISTS (
           SELECT title
             FROM ls_def_licence
            WHERE title = :title
               OR identifier = :uuid
       ) LIMIT 1;
xENDx;
        $insertLicence = $this->connection->prepare($sql);
        $licence_text = 'https://creativecommons.org/licenses/by/4.0/legalcode';
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $licence_text);
        $params = [
            'uuid' => $uuid->toString(),
            'uri' => 'local:'.$uuid->toString(),
            'title' => 'Attribution 4.0 International',
            'licence_text' => $licence_text,
            'description' => 'Creative Commons Attribution 4.0 International',
        ];
        $insertLicence->execute($params);
    }

    public function down(Schema $schema): void
    {
      if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
        $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
        return;
      }

        $this->addSql('DELETE IGNORE FROM ls_def_licence WHERE title = "Attribution 4.0 International" AND licence_text = "https://creativecommons.org/licenses/by/4.0/legalcode"');
    }
}
