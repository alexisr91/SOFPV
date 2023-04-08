<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230403094007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD transporter_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984F335C8B FOREIGN KEY (transporter_id) REFERENCES transporter (id)');
        $this->addSql('CREATE INDEX IDX_F52993984F335C8B ON `order` (transporter_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984F335C8B');
        $this->addSql('DROP INDEX IDX_F52993984F335C8B ON `order`');
        $this->addSql('ALTER TABLE `order` DROP transporter_id');
    }
}
