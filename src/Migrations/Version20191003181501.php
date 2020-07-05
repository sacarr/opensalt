<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191003181501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change concept_keywords to a JSON value';
    }

    public function up(Schema $schema): void
    {
      if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
        $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
        return;
    }

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<'xENDx'
UPDATE ls_item
   SET concept_keywords = CONCAT('["', concept_keywords, '"]')
 WHERE concept_keywords IS NOT NULL
;

UPDATE ls_item
   SET concept_keywords = REPLACE(concept_keywords, ',', '","')
 WHERE concept_keywords IS NOT NULL
;
xENDx
        );
        $this->addSql(<<<'xENDx'
UPDATE audit_ls_item
   SET concept_keywords = CONCAT('["', concept_keywords, '"]')
 WHERE concept_keywords IS NOT NULL
;

UPDATE audit_ls_item
   SET concept_keywords = REPLACE(concept_keywords, ',', '","')
 WHERE concept_keywords IS NOT NULL
;
xENDx
        );

        $this->addSql('ALTER TABLE ls_item CHANGE concept_keywords concept_keywords JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE audit_ls_item CHANGE concept_keywords concept_keywords JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
      if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
        $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
        return;
    }

        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit_ls_item CHANGE concept_keywords concept_keywords VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ls_item CHANGE concept_keywords concept_keywords VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci');

        $this->addSql(<<<'xENDx'
UPDATE ls_item
   SET concept_keywords = REPLACE(concept_keywords, '["', '')
 WHERE concept_keywords IS NOT NULL
;

UPDATE ls_item
   SET concept_keywords = REPLACE(concept_keywords, '"]', '')
 WHERE concept_keywords IS NOT NULL
;

UPDATE ls_item
   SET concept_keywords = REPLACE(concept_keywords, '","', ',')
 WHERE concept_keywords IS NOT NULL
;
xENDx
        );

        $this->addSql(<<<'xENDx'
UPDATE audit_ls_item
   SET concept_keywords = REPLACE(concept_keywords, '["', '')
 WHERE concept_keywords IS NOT NULL
;

UPDATE audit_ls_item
   SET concept_keywords = REPLACE(concept_keywords, '"]', '')
 WHERE concept_keywords IS NOT NULL
;

UPDATE audit_ls_item
   SET concept_keywords = REPLACE(concept_keywords, '","', ',')
 WHERE concept_keywords IS NOT NULL
;
xENDx
        );
    }
}
