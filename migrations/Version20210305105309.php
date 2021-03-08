<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210305105309 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE round (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, created DATETIME NOT NULL, ended DATETIME DEFAULT NULL, user1_board_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_board_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', board LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', removed_card INT NOT NULL, user1_action LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_action LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', set_number INT NOT NULL, user1_hand_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', user2_hand_cards LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', pioche LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_C5EEEA34E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE round ADD CONSTRAINT FK_C5EEEA34E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE round');
    }
}
