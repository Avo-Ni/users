<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121062323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_privilege ADD resource_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_privilege ADD CONSTRAINT FK_87C0176389329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('CREATE INDEX IDX_87C0176389329D25 ON user_privilege (resource_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_privilege DROP FOREIGN KEY FK_87C0176389329D25');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP INDEX IDX_87C0176389329D25 ON user_privilege');
        $this->addSql('ALTER TABLE user_privilege DROP resource_id');
    }
}
