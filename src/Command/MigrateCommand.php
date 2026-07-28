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

class MigrateCommand extends AbstractCommand
{
    use CommonTrait;

    protected function configure(): void
    {
        $this->configureCommonOptions();

        $this
            ->setName('phinx:migrate')
            ->setAliases(['p:m'])
            ->setDescription('Migrate the database')
            ->addOption(
                '--target',
                '-t',
                InputOption::VALUE_REQUIRED,
                'The version number to migrate to'
            )
            ->addOption(
                '--date',
                '-d',
                InputOption::VALUE_REQUIRED,
                'The date to migrate to'
            )
            ->addOption('--dry-run', '-x', InputOption::VALUE_NONE, 'Dump query to standard output instead of executing it')
            ->addOption('--fake', null, InputOption::VALUE_NONE, "Mark any migrations selected as run, but don't actually execute them")
            ->setHelp(
                <<<EOT
The <info>migrate</info> command runs all available migrations, optionally up to a specific version

<info>phinx migrate -e development</info>
<info>phinx migrate -e development -t 20110103081132</info>
<info>phinx migrate -e development -d 20110103</info>
<info>phinx migrate -e development -v</info>

EOT
            )
        ;
    }

    /**
     * Migrate the database.
     *
     * @return int integer 0 on success, or an error code
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initialize($input, $output);

        $version = $input->getOption('target');
        $date = $input->getOption('date');
        $fake = (bool) $input->getOption('fake');

        $envOptions = $this->getConfig()->getEnvironment('default');

        if (isset($envOptions['table_prefix'])) {
            $output->writeln('<info>using table prefix</info> '.$envOptions['table_prefix'], $this->verbosityLevel);
        }
        if (isset($envOptions['table_suffix'])) {
            $output->writeln('<info>using table suffix</info> '.$envOptions['table_suffix'], $this->verbosityLevel);
        }

        // run the migrations
        $start = microtime(true);
        if (null !== $date) {
            $this->getManager()->migrateToDateTime('default', new \DateTime($date), $fake);
        } else {
            $this->getManager()->migrate('default', $version, $fake);
        }
        $end = microtime(true);

        $output->writeln('', $this->verbosityLevel);
        $output->writeln('<comment>All Done. Took '.\sprintf('%.4fs', $end - $start).'</comment>', $this->verbosityLevel);

        return self::CODE_SUCCESS;
    }
}
