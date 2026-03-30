<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create devices and motion_events tables for Arduino motion tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE devices (id SERIAL NOT NULL, device_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(32) NOT NULL, last_seen TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, online BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8421FB6394A4C7D7 ON devices (device_id)');
        $this->addSql('CREATE INDEX IDX_8421FB63BF700BD ON devices (status)');

        $this->addSql('CREATE TABLE motion_events (id SERIAL NOT NULL, device_id INT NOT NULL, detected_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8905D86A94A4C7D7 ON motion_events (device_id)');
        $this->addSql('ALTER TABLE motion_events ADD CONSTRAINT FK_8905D86A94A4C7D7 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE motion_events DROP CONSTRAINT FK_8905D86A94A4C7D7');
        $this->addSql('DROP TABLE motion_events');
        $this->addSql('DROP TABLE devices');
    }
}

