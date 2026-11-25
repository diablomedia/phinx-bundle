<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\Command;

use DiabloMedia\PhinxBundle\Command\MigrateCommand;
use DiabloMedia\PhinxBundle\DependencyInjection\DiabloMediaPhinxExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

final class MigrationEnvironmentTest extends TestCase
{
    #[DataProvider('kernelEnvironments')]
    public function testMigrationReceivesKernelEnvironment(string $environment): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('The migration test requires PDO SQLite.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__);
        $container->setParameter('kernel.environment', $environment);
        (new DiabloMediaPhinxExtension())->load([[
            'paths' => ['migrations' => __DIR__.'/Fixtures/Migrations'],
            'environment' => ['connection' => ['dsn' => 'sqlite://localhost/:memory:']],
        ]], $container);
        $container->compile();

        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getContainer')->willReturn($container);
        $kernel->method('getEnvironment')->willReturn($environment);

        $application = new Application($kernel);
        $command = new MigrateCommand();
        $application->add($command);
        $tester = new CommandTester($command);

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('Migration environment: '.$environment."\n", $tester->getDisplay(true));
    }

    /** @return iterable<string, array{string}> */
    public static function kernelEnvironments(): iterable
    {
        yield 'testing' => ['testing'];
        yield 'production' => ['production'];
        yield 'development' => ['development'];
        yield 'custom name matching a Phinx config key' => ['default_migration_table'];
    }
}
