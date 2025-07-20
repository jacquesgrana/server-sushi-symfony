<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720161253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_form_prospect (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, comment VARCHAR(512) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contact_form ADD contact_form_prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_form ADD CONSTRAINT FK_7A777FB0E7139C8F FOREIGN KEY (contact_form_prospect_id) REFERENCES contact_form_prospect (id)');
        $this->addSql('CREATE INDEX IDX_7A777FB0E7139C8F ON contact_form (contact_form_prospect_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact_form_prospect');
        $this->addSql('ALTER TABLE contact_form DROP FOREIGN KEY FK_7A777FB0E7139C8F');
        $this->addSql('DROP INDEX IDX_7A777FB0E7139C8F ON contact_form');
        $this->addSql('ALTER TABLE contact_form DROP contact_form_prospect_id');
    }
}
