<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701143816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_form (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, prospect_id INT NOT NULL, INDEX IDX_7A777FB0D182060A (prospect_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contact_form ADD CONSTRAINT FK_7A777FB0D182060A FOREIGN KEY (prospect_id) REFERENCES contact_form_prospect (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_form DROP FOREIGN KEY FK_7A777FB0D182060A');
        $this->addSql('DROP TABLE contact_form');
    }
}
