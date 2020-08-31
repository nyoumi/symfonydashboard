<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824142153 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dati_transaction (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) DEFAULT NULL, currency_sent VARCHAR(255) DEFAULT NULL, amount_sent VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, sender_phone VARCHAR(255) NOT NULL, sender_country_code VARCHAR(255) NOT NULL, recipient_country_code VARCHAR(255) NOT NULL, recipient_phone VARCHAR(255) NOT NULL, recipient_account_num VARCHAR(255) DEFAULT NULL, account_ref VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, last_requested_at DATETIME DEFAULT NULL, step VARCHAR(255) DEFAULT NULL, step_description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE dati_transaction');
    }
}
