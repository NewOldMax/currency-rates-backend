<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170904110147 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE tokens (id VARCHAR(255) NOT NULL, token TEXT NOT NULL, expiredAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE refresh_tokens (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, token TEXT NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
        $this->addSql('CREATE TABLE users (id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, createdAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, role VARCHAR(50) NOT NULL, googleId VARCHAR(255) DEFAULT NULL, seed TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX user_idx ON users (email, googleId)');
        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE refresh_tokens DROP CONSTRAINT FK_9BACE7E1A76ED395');
        $this->addSql('DROP TABLE tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE users');
    }
}
