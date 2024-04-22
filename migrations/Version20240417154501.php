<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417154501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Created filter and measurement tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS filter (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type VARCHAR(1) NOT NULL, abbreviation VARCHAR(10) NOT NULL, first_line LONGTEXT DEFAULT NULL, second_line LONGTEXT DEFAULT NULL, air_quality_index NUMERIC(15, 7) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deviation_white NUMERIC(10, 7) DEFAULT NULL, deviation_black NUMERIC(10, 7) DEFAULT NULL, filtered_volume NUMERIC(10, 7) DEFAULT NULL, solid_relationship NUMERIC(10, 7) DEFAULT NULL, method VARCHAR(255), INDEX IDX_7FC45F1DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS measurement (id INT AUTO_INCREMENT NOT NULL, filter_id INT NOT NULL, type VARCHAR(1) NOT NULL, value NUMERIC(10, 7) NOT NULL, measured_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', temperature NUMERIC(10, 2) DEFAULT NULL, humidity NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_2CE0D811D395B25E (filter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE measurement ADD CONSTRAINT FK_2CE0D811D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter DROP FOREIGN KEY FK_7FC45F1DA76ED395');
        $this->addSql('ALTER TABLE measurement DROP FOREIGN KEY FK_2CE0D811D395B25E');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE measurement');
    }
}
