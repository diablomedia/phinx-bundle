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

class BreakpointCommand extends AbstractCommand
{
    use CommonTrait;

    public function configure(): void
    {
        $this->configureCommonOptions();

        $this->setName('phinx:breakpoint')
            ->setAliases(['p:b'])
            ->setDescription('Manage breakpoints')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to set or clear a breakpoint against')
            ->addOption('--set', '-s', InputOption::VALUE_NONE, 'Set the breakpoint')
            ->addOption('--unset', '-u', InputOption::VALUE_NONE, 'Unset the breakpoint')
            ->addOption('--remove-all', '-r', InputOption::VALUE_NONE, 'Remove all breakpoints')
            ->setHelp(
                <<<EOT
The <info>breakpoint</info> command allows you to set or clear a breakpoint against a specific target to inhibit rollbacks beyond a certain target.
If no target is supplied then the most recent migration will be used.
You cannot specify un-migrated targets

<info>phinx breakpoint</info>
<info>phinx breakpoint -t 20110103081132</info>
<info>phinx breakpoint -r</info>
EOT
            );
    }

    /**
     * Toggle the breakpoint.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $input->getOption('target');
        $removeAll = $input->getOption('remove-all');
        $set = $input->getOption('set');
        $unset = $input->getOption('unset');

        if ($version && $removeAll) {
            throw new \InvalidArgumentException('Cannot toggle a breakpoint and remove all breakpoints at the same time.');
        }

        if (($set && $unset) || ($set && $removeAll) || ($unset && $removeAll)) {
            throw new \InvalidArgumentException('Cannot use more than one of --set, --unset, or --remove-all at the same time.');
        }

        if ($removeAll) {
            $this->getManager()->removeBreakpoints('default');
        } elseif ($set) {
            $this->getManager()->setBreakpoint('default', $version);
        } elseif ($unset) {
            $this->getManager()->unsetBreakpoint('default', $version);
        } else {
            $this->getManager()->toggleBreakpoint('default', $version);
        }

        return self::CODE_SUCCESS;
    }
}
