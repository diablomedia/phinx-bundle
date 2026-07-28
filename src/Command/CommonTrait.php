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

use DiabloMedia\PhinxBundle\Config\PhinxConfig;
use Phinx\Db\Adapter\AdapterFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * common code for commands.
 *
 * @author Miha Vrhovnik <miha.vrhovnik@gmail.com>
 */
trait CommonTrait
{
    protected function configureCommonOptions(): void
    {
        $this->addOption('--no-info', null, InputOption::VALUE_NONE, 'Hides all debug information');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('no-info')) {
            $this->verbosityLevel = OutputInterface::VERBOSITY_VERBOSE;
        }

        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new \LogicException('Phinx commands must run within a Symfony FrameworkBundle application.');
        }

        $container = $application->getKernel()->getContainer();
        $config = $container->get('phinx.config');
        if (!$config instanceof PhinxConfig) {
            throw new \LogicException('The phinx.config service must use the bundle PhinxConfig class.');
        }

        if ($this->requiresDatabaseConnection() && $container->getParameter('phinx.prompt_password')) {
            if (!$input->isInteractive()) {
                throw new \RuntimeException('A database password prompt is configured, but the command is running non-interactively.');
            }

            $question = new Question($this->getDatabasePasswordPrompt($config));
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(static function (mixed $password): string {
                if (!\is_string($password) || '' === $password) {
                    throw new \RuntimeException('The database password cannot be empty.');
                }

                return $password;
            });

            $password = $this->getQuestionHelper()->ask($input, $output, $question);
            $config->setEnvironmentPassword('default', $password);
        }

        $this->setConfig($config);
        $this->loadManager($input, $output);

        $adapters = $container->getParameter('phinx.adapters');
        foreach ($adapters as $name => $class) {
            AdapterFactory::instance()->registerAdapter($name, $class);
        }
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        $helper = $this->getHelper('question');
        if (!$helper instanceof QuestionHelper) {
            throw new \LogicException('The Symfony question helper is not available.');
        }

        return $helper;
    }

    private function getDatabasePasswordPrompt(PhinxConfig $config): string
    {
        $environment = $config->getEnvironment('default');
        $user = $environment['user'] ?? null;
        $host = $environment['host'] ?? null;

        if (\is_string($user) && '' !== $user && \is_string($host) && '' !== $host) {
            return \sprintf(
                'Password for %s@%s: ',
                OutputFormatter::escape($user),
                OutputFormatter::escape($host),
            );
        }

        return 'Database password: ';
    }

    protected function requiresDatabaseConnection(): bool
    {
        return true;
    }
}
