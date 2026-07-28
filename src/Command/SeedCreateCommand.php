<?php
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

use InvalidArgumentException;
use Phinx\Config\NamespaceAwareInterface;
use Phinx\Console\Command\AbstractCommand;
use Phinx\Util\Util;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Phinx\Seed\AbstractSeed;

class SeedCreateCommand extends AbstractCommand
{
    use CommonTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('phinx:seed:create')
            ->setAliases(['p:s:c'])
            ->setDescription('Create a new seeder')
            ->addArgument('seederName', InputArgument::REQUIRED, 'What is the name of the seeder (in CamelCase)?')
            ->setHelp(
                <<<EOT
The <info>seed create</info> command create a new seeder

<info>phinx seed:create -e development MyNewSeeder</info>
<info>phinx seed:create -e development MyNewSeeder -t relative/path/to/template</info>

EOT
            );

        // An alternative template.
        $this->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Use an alternative template');
    }

    /**
     * Get the confirmation question asking if the user wants to create the
     * seeders directory.
     *
     * @return ConfirmationQuestion
     */
    protected function getCreateSeederDirectoryQuestion(): ConfirmationQuestion
    {
        return new ConfirmationQuestion('Create seeders directory? [y]/n ', true);
    }

    /**
     * Create the new seeder.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return integer
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initialize($input, $output);

        // get the seed path from the config
        $path = $this->getConfig()->getSeedPaths();
        $path = \array_pop($path);

        if (!\file_exists($path)) {
            $helper = $this->getHelper('question');
            $question = $this->getCreateSeederDirectoryQuestion();

            if ($helper->ask($input, $output, $question)) {
                \mkdir($path, 0755, true);
            }
        }

        $this->verifySeedDirectory($path);

        $path = \realpath($path);
        $className = $input->getArgument('seederName');

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'The seeder class name "%s" is invalid. Please use CamelCase format.',
                    $className
                )
            );
        }

        // Compute the file path
        $filePath = $path . DIRECTORY_SEPARATOR . $className . '.php';

        if (\is_file($filePath)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'The file "%s" already exists',
                    $filePath
                )
            );
        }

        $template = $this->getSeedTemplateFilename();
        $altTemplate = $input->getOption('template');
        if (null !== $altTemplate && \file_exists($altTemplate)) {
            $template = $altTemplate;
        }

        // inject the class names appropriate to this seeder
        $contents = \file_get_contents($template);

        $config = $this->getConfig();
        $namespace = $config instanceof NamespaceAwareInterface ? $config->getSeedNamespaceByPath($path) : null;
        $classes = [
            '$namespaceDefinition' => null !== $namespace ? ('namespace ' . $namespace . ';') : '',
            '$namespace'           => $namespace,
            '$useClassName'        => AbstractSeed::class,
            '$className'           => $className,
            '$baseClassName'       => 'AbstractSeed',
        ];
        $contents = \strtr($contents, $classes);

        if (false === \file_put_contents($filePath, $contents)) {
            throw new RuntimeException(\sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }

        $output->writeln('<info>using seed base class</info> ' . $classes['$useClassName']);
        if (null !== $altTemplate && \file_exists($altTemplate)) {
            $output->writeln('<info>using alternative template</info> ' . $altTemplate);
        } else {
            $output->writeln('<info>using default template</info>');
        }
        $output->writeln('<info>created</info> .' . \str_replace(\getcwd(), '', $filePath));

        return self::CODE_SUCCESS;
    }
}
