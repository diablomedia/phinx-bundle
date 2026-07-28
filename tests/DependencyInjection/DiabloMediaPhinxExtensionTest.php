<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\DependencyInjection;

use DiabloMedia\PhinxBundle\Command\BreakpointCommand;
use DiabloMedia\PhinxBundle\Command\CreateCommand;
use DiabloMedia\PhinxBundle\Command\MigrateCommand;
use DiabloMedia\PhinxBundle\Command\RollbackCommand;
use DiabloMedia\PhinxBundle\Command\SeedCreateCommand;
use DiabloMedia\PhinxBundle\Command\SeedRunCommand;
use DiabloMedia\PhinxBundle\Command\StatusCommand;
use DiabloMedia\PhinxBundle\DependencyInjection\DiabloMediaPhinxExtension;
use Phinx\Config\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DiabloMediaPhinxExtensionTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.project_dir', '/project');
    }

    public function testLoadRegistersPhinxConfiguration(): void
    {
        $this->loadExtension([
            'migration_base_class' => 'App\\Migration',
            'adapters' => ['custom' => 'App\\Database\\CustomAdapter'],
            'paths' => ['migrations' => '/migrations', 'seeds' => '/seeds'],
            'environment' => [
                'migration_table' => 'migration_log',
                'table_prefix' => 'prefix_',
                'table_suffix' => '_suffix',
                'connection' => ['dsn' => 'sqlite::memory:'],
            ],
        ]);

        $definition = $this->container->getDefinition('phinx.config');
        self::assertSame(Config::class, $definition->getClass());
        self::assertTrue($definition->isPublic());
        self::assertSame(['custom' => 'App\\Database\\CustomAdapter'], $this->container->getParameter('phinx.adapters'));

        $arguments = $definition->getArguments();
        self::assertCount(1, $arguments);
        self::assertIsArray($arguments[0]);
        self::assertSame('default', $arguments[0]['environments']['default_database']);
        self::assertSame('migration_log', $arguments[0]['environments']['default_migration_table']);
        self::assertSame('sqlite::memory:', $arguments[0]['environments']['default']['dsn']);
        self::assertSame('prefix_', $arguments[0]['environments']['default']['table_prefix']);
        self::assertSame('_suffix', $arguments[0]['environments']['default']['table_suffix']);
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('commandServiceProvider')]
    public function testLoadRegistersCommandServices(string $serviceId, string $class): void
    {
        $this->loadExtension();

        self::assertTrue($this->container->hasDefinition($serviceId));
        self::assertSame($class, $this->container->getDefinition($serviceId)->getClass());
        self::assertNotSame([], $this->container->getDefinition($serviceId)->getTag('console.command'));
    }

    /**
     * @return iterable<string, array{string, class-string}>
     */
    public static function commandServiceProvider(): iterable
    {
        yield 'breakpoint' => ['diablomedia_phinx.command.breakpoint_command', BreakpointCommand::class];
        yield 'create' => ['diablomedia_phinx.command.create_command', CreateCommand::class];
        yield 'migrate' => ['diablomedia_phinx.command.migrate_command', MigrateCommand::class];
        yield 'rollback' => ['diablomedia_phinx.command.rollback_command', RollbackCommand::class];
        yield 'seed create' => ['diablomedia_phinx.command.seed_create_command', SeedCreateCommand::class];
        yield 'seed run' => ['diablomedia_phinx.command.seed_run_command', SeedRunCommand::class];
        yield 'status' => ['diablomedia_phinx.command.status_command', StatusCommand::class];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadExtension(array $config = []): void
    {
        $config['environment']['connection']['dsn'] ??= 'sqlite::memory:';

        (new DiabloMediaPhinxExtension())->load([$config], $this->container);
    }
}
