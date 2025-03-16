<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250316052858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/Drop action table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "action" (id SERIAL NOT NULL, type VARCHAR(128) NOT NULL, actor VARCHAR(128) NOT NULL, payload JSON DEFAULT NULL, status VARCHAR(16) NOT NULL, details JSON DEFAULT NULL, created_at BIGINT NOT NULL, updated_at BIGINT NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //$this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE "action"');
    }
}
