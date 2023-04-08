<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230402145114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart ADD ordering_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B78E6C7DE4 FOREIGN KEY (ordering_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_BA388B78E6C7DE4 ON cart (ordering_id)');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993981AD5CDBF');
        $this->addSql('DROP INDEX UNIQ_F52993981AD5CDBF ON `order`');
        $this->addSql('ALTER TABLE `order` DROP cart_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B78E6C7DE4');
        $this->addSql('DROP INDEX IDX_BA388B78E6C7DE4 ON cart');
        $this->addSql('ALTER TABLE cart DROP ordering_id');
        $this->addSql('ALTER TABLE `order` ADD cart_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993981AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993981AD5CDBF ON `order` (cart_id)');
    }
}
