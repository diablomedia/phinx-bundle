<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\Command;

use DiabloMedia\PhinxBundle\Command\BreakpointCommand;
use DiabloMedia\PhinxBundle\Command\CreateCommand;
use DiabloMedia\PhinxBundle\Command\MigrateCommand;
use DiabloMedia\PhinxBundle\Command\RollbackCommand;
use DiabloMedia\PhinxBundle\Command\SeedCreateCommand;
use DiabloMedia\PhinxBundle\Command\SeedRunCommand;
use DiabloMedia\PhinxBundle\Command\StatusCommand;
use Phinx\Console\Command\Breakpoint as PhinxBreakpointCommand;
use Phinx\Console\Command\Create as PhinxCreateCommand;
use Phinx\Console\Command\Migrate as PhinxMigrateCommand;
use Phinx\Console\Command\Rollback as PhinxRollbackCommand;
use Phinx\Console\Command\SeedCreate as PhinxSeedCreateCommand;
use Phinx\Console\Command\SeedRun as PhinxSeedRunCommand;
use Phinx\Console\Command\Status as PhinxStatusCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

final class CommandConfigurationTest extends TestCase
{
    /**
     * @param class-string<Command> $commandClass
     * @param list<string>          $aliases
     * @param list<string>          $options
     * @param list<string>          $arguments
     */
    #[DataProvider('commandProvider')]
    public function testCommandConfiguration(
        string $commandClass,
        string $name,
        array $aliases,
        array $options,
        array $arguments = [],
    ): void {
        $command = new $commandClass();

        self::assertSame($name, $command->getName());
        self::assertSame($aliases, $command->getAliases());

        foreach ($options as $option) {
            self::assertTrue($command->getDefinition()->hasOption($option));
        }

        foreach ($arguments as $argument) {
            self::assertTrue($command->getDefinition()->hasArgument($argument));
        }

        self::assertNotSame('', $command->getDescription());
        self::assertNotSame('', $command->getHelp());
    }

    /**
     * @return iterable<string, array{class-string<Command>, string, list<string>, list<string>, list<string>}>
     */
    public static function commandProvider(): iterable
    {
        yield 'breakpoint' => [BreakpointCommand::class, 'phinx:breakpoint', ['p:b'], ['no-info', 'target', 'set', 'unset', 'remove-all'], []];
        yield 'create' => [CreateCommand::class, 'phinx:create', ['p:c'], ['no-info', 'template', 'class', 'path', 'style'], ['migrationName']];
        yield 'migrate' => [MigrateCommand::class, 'phinx:migrate', ['p:m'], ['no-info', 'target', 'date', 'dry-run', 'fake'], []];
        yield 'rollback' => [RollbackCommand::class, 'phinx:rollback', ['p:r'], ['no-info', 'target', 'date', 'force', 'dry-run', 'fake'], []];
        yield 'seed create' => [SeedCreateCommand::class, 'phinx:seed:create', ['p:s:c'], ['no-info', 'template', 'path'], ['seederName']];
        yield 'seed run' => [SeedRunCommand::class, 'phinx:seed:run', ['p:s:r'], ['no-info', 'seed'], []];
        yield 'status' => [StatusCommand::class, 'phinx:status', ['p:s'], ['no-info', 'format'], []];
    }

    /**
     * @param class-string<Command> $phinxCommandClass
     * @param class-string<Command> $bundleCommandClass
     */
    #[DataProvider('phinxCommandProvider')]
    public function testSupportsEveryApplicablePhinxOption(
        string $phinxCommandClass,
        string $bundleCommandClass,
    ): void {
        $phinxCommand = new $phinxCommandClass();
        $bundleCommand = new $bundleCommandClass();
        $inapplicableOptions = ['configuration', 'parser', 'environment'];

        foreach ($phinxCommand->getDefinition()->getOptions() as $option) {
            if (\in_array($option->getName(), $inapplicableOptions, true)) {
                continue;
            }

            self::assertTrue(
                $bundleCommand->getDefinition()->hasOption($option->getName()),
                \sprintf('%s does not support Phinx option --%s.', $bundleCommandClass, $option->getName()),
            );
        }
    }

    /**
     * @return iterable<string, array{class-string<Command>, class-string<Command>}>
     */
    public static function phinxCommandProvider(): iterable
    {
        yield 'breakpoint' => [PhinxBreakpointCommand::class, BreakpointCommand::class];
        yield 'create' => [PhinxCreateCommand::class, CreateCommand::class];
        yield 'migrate' => [PhinxMigrateCommand::class, MigrateCommand::class];
        yield 'rollback' => [PhinxRollbackCommand::class, RollbackCommand::class];
        yield 'seed create' => [PhinxSeedCreateCommand::class, SeedCreateCommand::class];
        yield 'seed run' => [PhinxSeedRunCommand::class, SeedRunCommand::class];
        yield 'status' => [PhinxStatusCommand::class, StatusCommand::class];
    }
}
