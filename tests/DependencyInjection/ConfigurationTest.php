<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\DependencyInjection;

use DiabloMedia\PhinxBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultsAreApplied(): void
    {
        $config = (new Processor())->processConfiguration(
            new Configuration(),
            [[]],
        );

        self::assertSame('%kernel.project_dir%/src/Resources/db/migrations', $config['paths']['migrations']);
        self::assertSame('%kernel.project_dir%/src/Resources/db/seeds', $config['paths']['seeds']);
        self::assertSame(['prompt_password' => false], $config['environment']);
    }

    public function testCustomConfigurationIsPreserved(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'migration_base_class' => 'App\\Migration',
            'adapters' => ['custom' => 'App\\Database\\CustomAdapter'],
            'paths' => [
                'migrations' => '/migrations',
                'seeds' => '/seeds',
            ],
            'environment' => [
                'table_prefix' => 'prefix_',
                'table_suffix' => '_suffix',
                'migration_table' => 'migration_log',
                'prompt_password' => true,
                'connection' => ['dsn' => 'sqlite::memory:'],
            ],
        ]]);

        self::assertSame('App\\Migration', $config['migration_base_class']);
        self::assertSame(['custom' => 'App\\Database\\CustomAdapter'], $config['adapters']);
        self::assertSame('/migrations', $config['paths']['migrations']);
        self::assertSame('/seeds', $config['paths']['seeds']);
        self::assertSame('prefix_', $config['environment']['table_prefix']);
        self::assertSame('_suffix', $config['environment']['table_suffix']);
        self::assertSame('migration_log', $config['environment']['migration_table']);
        self::assertTrue($config['environment']['prompt_password']);
    }

    public function testDsnIsRequired(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(
            new Configuration(),
            [['environment' => ['connection' => []]]],
        );
    }
}
