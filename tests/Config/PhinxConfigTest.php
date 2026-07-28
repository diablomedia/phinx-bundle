<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\Config;

use DiabloMedia\PhinxBundle\Config\PhinxConfig;
use PHPUnit\Framework\TestCase;

final class PhinxConfigTest extends TestCase
{
    public function testEnvironmentPasswordCanBeAddedAtRuntime(): void
    {
        $config = new PhinxConfig([
            'environments' => [
                'default_environment' => 'default',
                'default' => [
                    'dsn' => 'mysql://db_user@127.0.0.1:3306/db_name',
                ],
            ],
        ]);

        $config->setEnvironmentPassword('default', 'secret');

        $environment = $config->getEnvironment('default');
        self::assertIsArray($environment);
        self::assertSame('secret', $environment['pass']);
    }

    public function testUnknownEnvironmentCannotBeModified(): void
    {
        $config = new PhinxConfig(['environments' => []]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The Phinx environment "default" does not exist.');

        $config->setEnvironmentPassword('default', 'secret');
    }
}
