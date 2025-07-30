<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250730151819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, rank INT NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(100) NOT NULL, intro VARCHAR(512) DEFAULT NULL, text VARCHAR(1024) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, author_id INT DEFAULT NULL, INDEX IDX_BA5AE01DF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE blog_post_blog_tag (blog_post_id INT NOT NULL, blog_tag_id INT NOT NULL, INDEX IDX_CA877A44A77FBEAF (blog_post_id), INDEX IDX_CA877A442F9DC6D0 (blog_tag_id), PRIMARY KEY(blog_post_id, blog_tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE blog_tag (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE blog_post_blog_tag ADD CONSTRAINT FK_CA877A44A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_blog_tag ADD CONSTRAINT FK_CA877A442F9DC6D0 FOREIGN KEY (blog_tag_id) REFERENCES blog_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01DF675F31B');
        $this->addSql('ALTER TABLE blog_post_blog_tag DROP FOREIGN KEY FK_CA877A44A77FBEAF');
        $this->addSql('ALTER TABLE blog_post_blog_tag DROP FOREIGN KEY FK_CA877A442F9DC6D0');
        $this->addSql('DROP TABLE blog_post');
        $this->addSql('DROP TABLE blog_post_blog_tag');
        $this->addSql('DROP TABLE blog_tag');
    }
}
