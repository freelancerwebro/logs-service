<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250320135829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add log table unique index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX uniq_logs_service_method_endpoint_status_created ON log (service_name, method, endpoint, status_code, created)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_logs_service_method_endpoint_status_created ON log');
    }
}
