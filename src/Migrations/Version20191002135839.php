<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191002135839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop unused subject_uri column.';
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
UPDATE ls_doc
   SET subject = CONCAT('["', subject, '"]')
 WHERE subject IS NOT NULL
xENDx
        );
        $this->addSql(<<<'xENDx'
UPDATE audit_ls_doc
   SET subject = CONCAT('["', subject, '"]')
 WHERE subject IS NOT NULL
xENDx
        );

        $this->addSql(<<<'xENDx'
ALTER TABLE ls_doc
  DROP subject_uri,
  CHANGE subject subject JSON DEFAULT NULL COMMENT '(DC2Type:json)'
xENDx
        );
        $this->addSql(<<<'xENDx'
ALTER TABLE audit_ls_doc
  DROP subject_uri,
  CHANGE subject subject JSON DEFAULT NULL COMMENT '(DC2Type:json)'
xENDx
        );
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
                $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
                return;
        }
   
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
ALTER TABLE audit_ls_doc
  ADD subject_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
  CHANGE subject subject VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci
        ');

        $this->addSql('
ALTER TABLE ls_doc
  ADD subject_uri VARCHAR(300) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
  CHANGE subject subject VARCHAR(50) DEFAULT NULL COLLATE utf8mb4_unicode_ci
        ');

        $this->addSql(<<<'xENDx'
UPDATE ls_doc
   SET subject = REPLACE(subject, '["', '')
 WHERE subject IS NOT NULL
;

UPDATE ls_doc
   SET subject = REPLACE(subject, '"]', '')
 WHERE subject IS NOT NULL
;
xENDx
        );

        $this->addSql(<<<'xENDx'
UPDATE audit_ls_doc
   SET subject = REPLACE(subject, '["', '')
 WHERE subject IS NOT NULL
;

UPDATE audit_ls_doc
   SET subject = REPLACE(subject, '"]', '')
 WHERE subject IS NOT NULL
;
xENDx
        );
    }
}
