<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120212243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shift (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, type VARCHAR(20) NOT NULL, restaurant_id INTEGER NOT NULL, CONSTRAINT FK_A50B3B45B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A50B3B45B1E7706E ON shift (restaurant_id)');
        $this->addSql('CREATE TABLE shift_users (shift_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY (shift_id, user_id), CONSTRAINT FK_1F6D43B7BB70BC0E FOREIGN KEY (shift_id) REFERENCES shift (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1F6D43B7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1F6D43B7BB70BC0E ON shift_users (shift_id)');
        $this->addSql('CREATE INDEX IDX_1F6D43B7A76ED395 ON shift_users (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE shift');
        $this->addSql('DROP TABLE shift_users');
    }
}
