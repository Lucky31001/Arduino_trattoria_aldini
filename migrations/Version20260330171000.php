<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330171000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add notification_to column on devices for Twilio recipient number.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devices ADD notification_to VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devices DROP notification_to');
    }
}

