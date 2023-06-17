<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230722170811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_response_contact (id INT AUTO_INCREMENT NOT NULL, contact_id INT DEFAULT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, subject VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D98A0771E7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_response_contact ADD CONSTRAINT FK_D98A0771E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_response_contact DROP FOREIGN KEY FK_D98A0771E7A1254A');
        $this->addSql('DROP TABLE admin_response_contact');
    }
}
