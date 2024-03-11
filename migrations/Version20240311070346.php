<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240311070346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add log table indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_log_service_name ON log (service_name)');
        $this->addSql('CREATE INDEX idx_log_status_code ON log (status_code)');
        $this->addSql('CREATE INDEX idx_log_created ON log (created)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_log_service_name');
        $this->addSql('DROP INDEX idx_log_status_code');
        $this->addSql('DROP INDEX idx_log_created');
    }
}
