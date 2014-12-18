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
            ->children()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('field_config')->defaultValue('elements:title,content,tags;media:title,content,tags')->end()
                        ->integerNode('min_token_length')->defaultValue(4)->end()
                        ->booleanNode('skip_restricted')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('levenshtein')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('cost')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('insert')->defaultValue(3)->end()
                                ->integerNode('replace')->defaultValue(4)->end()
                                ->integerNode('delete')->defaultValue(4)->end()
                            ->end()
                        ->end()
                        ->floatNode('max')->defaultValue(1.33333)->end()
                    ->end()
                ->end()
                ->arrayNode('solr')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('partial')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable')->defaultValue(true)->end()
                                ->floatNode('boost')->defaultValue(2.0)->end()
                            ->end()
                        ->end()
                        ->arrayNode('fuzzy')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enable')->defaultValue(true)->end()
                                ->floatNode('boost')->defaultValue(1.0)->end()
                                ->floatNode('sim')->defaultValue(0.5)->end()
                                ->integerNode('min_length')->defaultValue(6)->end()
                            ->end()
                        ->end()
                        ->arrayNode('subquery')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('size')->defaultValue(25)->end()
                                ->integerNode('size_restricted')->defaultValue(1000)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('result')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('suggestions')->defaultValue(10)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
