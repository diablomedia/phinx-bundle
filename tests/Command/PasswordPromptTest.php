<?php

declare(strict_types=1);

namespace DiabloMedia\PhinxBundle\Tests\Command;

use DiabloMedia\PhinxBundle\Command\CommonTrait;
use DiabloMedia\PhinxBundle\Config\PhinxConfig;
use Phinx\Console\Command\AbstractCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

final class PasswordPromptTest extends TestCase
{
    public function testInteractiveCommandPromptsForHiddenPassword(): void
    {
        [$command, $config] = $this->createCommand();
        $tester = new CommandTester($command);
        $helper = $this->createMock(QuestionHelper::class);
        $helper->expects(self::once())
            ->method('ask')
            ->willReturnCallback(static function (
                InputInterface $input,
                OutputInterface $output,
                Question $question,
            ): string {
                self::assertSame('Password for db_user@127.0.0.1: ', $question->getQuestion());
                self::assertTrue($question->isHidden());
                self::assertFalse($question->isHiddenFallback());

                return 'secret';
            });
        $command->getHelperSet()->set($helper, 'question');

        self::assertSame(Command::SUCCESS, $tester->execute([]));

        $environment = $config->getEnvironment('default');
        self::assertIsArray($environment);
        self::assertSame('secret', $environment['pass']);
    }

    public function testNonInteractiveCommandFailsInsteadOfPrompting(): void
    {
        [$command] = $this->createCommand();
        $tester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'A database password prompt is configured, but the command is running non-interactively.',
        );

        $tester->execute([], ['interactive' => false]);
    }

    /**
     * @return array{PasswordPromptCommand, PhinxConfig}
     */
    private function createCommand(): array
    {
        $config = new PhinxConfig([
            'paths' => [
                'migrations' => __DIR__,
                'seeds' => __DIR__,
            ],
            'environments' => [
                'default_environment' => 'default',
                'default' => [
                    'dsn' => 'mysql://db_user@127.0.0.1:3306/db_name',
                ],
            ],
        ]);

        $container = new ContainerBuilder();
        $container->set('phinx.config', $config);
        $container->setParameter('phinx.prompt_password', true);
        $container->setParameter('phinx.adapters', []);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getContainer')->willReturn($container);

        $application = new Application($kernel);
        $command = new PasswordPromptCommand();
        $application->add($command);

        return [$command, $config];
    }
}

final class PasswordPromptCommand extends AbstractCommand
{
    use CommonTrait;

    protected function configure(): void
    {
        $this->configureCommonOptions();
        $this->setName('test:password-prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}
