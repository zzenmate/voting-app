<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170506180838 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vote_results (id INT AUTO_INCREMENT NOT NULL, vote_id INT NOT NULL, deputy_number INT NOT NULL, deputy_full_name VARCHAR(255) NOT NULL, result ENUM(\'true\', \'false\', \'absent\', \'abstained\', \'not_voted\') NOT NULL COMMENT \'(DC2Type:VoteResultType)\', INDEX IDX_1411BB8C72DCDAFC (vote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sessions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE votes (id INT AUTO_INCREMENT NOT NULL, session_id INT NOT NULL, name LONGTEXT NOT NULL, number INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_518B7ACF613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vote_results ADD CONSTRAINT FK_1411BB8C72DCDAFC FOREIGN KEY (vote_id) REFERENCES votes (id)');
        $this->addSql('ALTER TABLE votes ADD CONSTRAINT FK_518B7ACF613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE votes DROP FOREIGN KEY FK_518B7ACF613FECDF');
        $this->addSql('ALTER TABLE vote_results DROP FOREIGN KEY FK_1411BB8C72DCDAFC');
        $this->addSql('DROP TABLE vote_results');
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE votes');
    }
}
