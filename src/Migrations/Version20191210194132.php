<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Framework\LsAssociation;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191210194132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix some association types to be the correct value';
    }

    public function up(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $typeFixup = [
            'isChildOf' => LsAssociation::CHILD_OF,
            'exactMatchOf' => LsAssociation::EXACT_MATCH_OF,
            'isRelatedTo' => LsAssociation::RELATED_TO,
            'isPeerOf' => LsAssociation::IS_PEER_OF,
            'replacedBy' => LsAssociation::REPLACED_BY,
            'hasSkillLevel' => LsAssociation::SKILL_LEVEL,
            'isPartOf' => LsAssociation::PART_OF,
        ];

        foreach ($typeFixup as $currentName => $fixedName) {
            $this->addSql("UPDATE ls_association SET `type`='{$fixedName}' WHERE `type`='{$currentName}';");
            $this->addSql("UPDATE audit_ls_association SET `type`='{$fixedName}' WHERE `type`='{$currentName}';");
        }
    }

    public function down(Schema $schema): void
    {
        if ( $this->connection->getDatabasePlatform()->getName() == 'postgresql') {
            $this->addSql("SELECT 'Postgres migration skipped.  Postgres database is container-initialized'");
            return;
        }

        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Nothing to do
    }
}
