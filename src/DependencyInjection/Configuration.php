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

namespace DiabloMedia\PhinxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('diablomedia_phinx');

        $rootNode = $treeBuilder->getRootNode();
        if (!$rootNode instanceof ArrayNodeDefinition) {
            throw new \LogicException('The bundle configuration root must be an array node.');
        }

        $children = $rootNode->children();
        $children->scalarNode('migration_base_class')
            ->info('Replace default migration class');

        $adapters = $children->arrayNode('adapters');
        $adapters
            ->info('Replace or add migration adapters')
            ->useAttributeAsKey('name')
            ->scalarPrototype();

        $paths = $children->arrayNode('paths');
        $paths->addDefaultsIfNotSet();
        $pathChildren = $paths->children();
        $pathChildren->variableNode('migrations')
            ->defaultValue('%kernel.project_dir%/src/Resources/db/migrations');
        $pathChildren->variableNode('seeds')
            ->defaultValue('%kernel.project_dir%/src/Resources/db/seeds');

        $environment = $children->arrayNode('environment');
        $environment->isRequired();
        $environmentChildren = $environment->children();
        $environmentChildren->scalarNode('table_prefix');
        $environmentChildren->scalarNode('table_suffix');
        $environmentChildren->scalarNode('migration_table');

        $connection = $environmentChildren->arrayNode('connection');
        $connection->isRequired();
        $connection->children()
            ->scalarNode('dsn')
            ->isRequired();

        return $treeBuilder;
    }
}
