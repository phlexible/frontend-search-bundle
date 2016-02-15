<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Frontend search configuration
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
                ->arrayNode('results')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_search_route_name')->defaultNull()->end()
                        ->scalarNode('template')->defaultValue('PhlexibleFrontendSearchBundle::results.html.twig')->end()
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
