<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230215175729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE counter_user (counter_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C21E0F2EFCEEF2E3 (counter_id), INDEX IDX_C21E0F2EA76ED395 (user_id), PRIMARY KEY(counter_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE counter_user ADD CONSTRAINT FK_C21E0F2EFCEEF2E3 FOREIGN KEY (counter_id) REFERENCES counter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE counter_user ADD CONSTRAINT FK_C21E0F2EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE counter_user DROP FOREIGN KEY FK_C21E0F2EFCEEF2E3');
        $this->addSql('ALTER TABLE counter_user DROP FOREIGN KEY FK_C21E0F2EA76ED395');
        $this->addSql('DROP TABLE counter_user');
    }
}
