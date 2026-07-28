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
        yield 'breakpoint' => [BreakpointCommand::class, 'phinx:breakpoint', ['p:b'], ['target', 'remove-all'], []];
        yield 'create' => [CreateCommand::class, 'phinx:create', ['p:c'], ['template', 'class'], ['migrationName']];
        yield 'migrate' => [MigrateCommand::class, 'phinx:migrate', ['p:m'], ['target', 'date'], []];
        yield 'rollback' => [RollbackCommand::class, 'phinx:rollback', ['p:r'], ['target', 'date', 'force'], []];
        yield 'seed create' => [SeedCreateCommand::class, 'phinx:seed:create', ['p:s:c'], ['template'], ['seederName']];
        yield 'seed run' => [SeedRunCommand::class, 'phinx:seed:run', ['p:s:r'], ['seed'], []];
        yield 'status' => [StatusCommand::class, 'phinx:status', ['p:s'], ['format'], []];
    }
}
