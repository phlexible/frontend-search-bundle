<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Frontend search configuration.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('phlexible_frontend_search');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('index_name')->defaultValue('phlexible_elastica.index')->end()
                ->arrayNode('results')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_search_route_name')->defaultNull()->end()
                        ->scalarNode('template')->defaultValue('PhlexibleFrontendSearchBundle::results.html.twig')->end()
                        ->scalarNode('part_template')->defaultValue('PhlexibleFrontendSearchBundle::results_part.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('query_builder')->defaultValue('phlexible_frontend_search.query_string_query_builder')->end()
                    ->end()
                ->end()
                ->arrayNode('pager')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('PhlexibleFrontendSearchBundle::pager.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('suggestions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('size')->defaultValue(10)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
