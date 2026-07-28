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

class SeedRunCommand extends AbstractCommand
{
    use CommonTrait;

    protected function configure(): void
    {
        $this->configureCommonOptions();

        $this
            ->setName('phinx:seed:run')
            ->setAliases(['p:s:r'])
            ->setDescription('Seed the database')
            ->addOption(
                '--seed',
                '-s',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'The seed class(es) to run'
            )
            ->setHelp(
                <<<EOT
The <info>seed run</info> command runs all available seeds, optionally up to a specific class(es)

<info>phinx seed:run -e development</info>
<info>phinx seed:run -e development -s MySeeder -s MyOtherSeeder</info>

EOT
            );
    }

    /**
     * Seed the database.
     *
     * @return int integer 0 on success, or an error code
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initialize($input, $output);

        $seeds = $input->getOption('seed');

        // run the seeders
        $start = microtime(true);
        if (\count($seeds) > 0) {
            foreach ($seeds as $seed) {
                $this->getManager()->seed('default', $seed);
            }
        } else {
            $this->getManager()->seed('default');
        }
        $end = microtime(true);

        $output->writeln('', $this->verbosityLevel);
        $output->writeln('<comment>All Done. Took '.\sprintf('%.4fs', $end - $start).'</comment>', $this->verbosityLevel);

        return self::CODE_SUCCESS;
    }
}
