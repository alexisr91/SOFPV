<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221011204251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE drone ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE drone ADD CONSTRAINT FK_EE8B1164A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_EE8B1164A76ED395 ON drone (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE drone DROP FOREIGN KEY FK_EE8B1164A76ED395');
        $this->addSql('DROP INDEX IDX_EE8B1164A76ED395 ON drone');
        $this->addSql('ALTER TABLE drone DROP user_id');
    }
}
