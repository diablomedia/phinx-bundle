<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ReportEnvironment extends AbstractMigration
{
    public function up(): void
    {
        $this->getOutput()?->writeln('Migration environment: '.$this->getEnvironment());
    }
}
