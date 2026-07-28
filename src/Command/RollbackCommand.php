<?php

declare(strict_types=1);
/**
 * Released under the MIT License.
 *
 * Copyright (c) 2017 Miha Vrhovnik <miha.vrhovnik@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace DiabloMedia\PhinxBundle\Command;

use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RollbackCommand extends AbstractCommand
{
    use CommonTrait;
    /**
     * Exit code when the command is started but interrupted before execution.
     *
     * @var int
     */
    public const CODE_ABORTED = 4;

    protected function configure(): void
    {
        $this
            ->setName('phinx:rollback')
            ->setAliases(['p:r'])
            ->setDescription('Rollback the last or to a specific migration')
            ->addOption(
                '--target',
                '-t',
                InputOption::VALUE_REQUIRED,
                'The version number to rollback to'
            )
            ->addOption(
                '--date',
                '-d',
                InputOption::VALUE_REQUIRED,
                'The date to rollback to'
            )
            ->addOption(
                '--force',
                '-f',
                InputOption::VALUE_NONE,
                'Force rollback to ignore breakpoints'
            )
            ->setHelp(
                <<<EOT
The <info>rollback</info> command reverts the last migration, or optionally up to a specific version

<info>phinx rollback</info>
<info>phinx rollback -t 20111018185412</info>
<info>phinx rollback -d 20111018</info>
<info>phinx rollback -v</info>
<info>phinx rollback -t 20111018185412 -f</info>

If you have a breakpoint set, then you can rollback to target 0 and the rollbacks will stop at the breakpoint.
<info>phinx rollback -t 0 </info>

You can skip the confirmation by adding <info>--no-interaction</info>
EOT
            )
        ;
    }

    /**
     * Rollback the migration.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initialize($input, $output);

        $version = $input->getOption('target');
        $date = $input->getOption('date');
        $force = (bool) $input->getOption('force');
        $noInteraction = $input->getOption('no-interaction');

        if (!$noInteraction) {
            $options = $this->getManager()->getEnvironment('default')->getOptions();
            $helper = $this->getHelper('question');

            $message = '<info>Careful, would rollback the database <comment>%s</comment>. '
                .'Do you want to continue? (yes/no)</info> [<comment>no</comment>]: ';
            $question = new ConfirmationQuestion(\sprintf($message, $options['name']), false);
            $question->setAutocompleterValues(['yes', 'no']);

            if (!$helper->ask($input, $output, $question)) {
                return self::CODE_ABORTED;
            }
        }

        // rollback the specified environment
        $start = microtime(true);
        if (null !== $date) {
            $this->getManager()->rollbackToDateTime('default', new \DateTime($date), $force);
        } else {
            $this->getManager()->rollback('default', $version, $force);
        }
        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took '.\sprintf('%.4fs', $end - $start).'</comment>');

        return self::CODE_SUCCESS;
    }
}
