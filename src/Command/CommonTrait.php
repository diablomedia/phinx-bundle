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

use Phinx\Db\Adapter\AdapterFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * common code for commands.
 *
 * @author Miha Vrhovnik <miha.vrhovnik@gmail.com>
 */
trait CommonTrait
{
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new \LogicException('Phinx commands must run within a Symfony FrameworkBundle application.');
        }

        $container = $application->getKernel()->getContainer();
        $this->setConfig($container->get('phinx.config'));
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
}
