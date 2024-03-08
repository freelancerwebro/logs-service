<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308052914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table log_line';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `log_line` (
            id INT AUTO_INCREMENT NOT NULL, 
            service_name VARCHAR(30) NOT NULL, 
            method VARCHAR(10) NOT NULL, 
            endpoint VARCHAR(255) NOT NULL, 
            status_code SMALLINT NOT NULL, 
            created DATETIME NOT NULL, 
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `log_line`');
    }
}
