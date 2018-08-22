<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\ContactFormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * DI Configuration Class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('c975_l_contact_form');

        $rootNode
            ->children()
                ->scalarNode('site')
                ->end()
                ->scalarNode('sentTo')
                ->end()
                ->booleanNode('database')
                    ->defaultFalse()
                ->end()
                ->booleanNode('gdpr')
                    ->defaultTrue()
                ->end()
                ->integerNode('delay')
                    ->defaultValue(7)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
