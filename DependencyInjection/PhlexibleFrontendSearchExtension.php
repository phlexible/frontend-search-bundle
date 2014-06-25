<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Frontend search extension
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PhlexibleFrontendSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('search.yml');
        $loader->load('suggest.yml');

        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);

        $container->setParameter('phlexible_frontend_search.use_context', $config['use_context']);
        $container->setParameter('phlexible_frontend_search.query.field_config', $config['query']['field_config']);
        $container->setParameter('phlexible_frontend_search.query.min_token_length', $config['query']['min_token_length']);
        $container->setParameter('phlexible_frontend_search.query.skip_restricted', $config['query']['skip_restricted']);
        $container->setParameter('phlexible_frontend_search.levenshtein.cost.insert', $config['levenshtein']['cost']['insert']);
        $container->setParameter('phlexible_frontend_search.levenshtein.cost.replace', $config['levenshtein']['cost']['replace']);
        $container->setParameter('phlexible_frontend_search.levenshtein.cost.delete', $config['levenshtein']['cost']['delete']);
        $container->setParameter('phlexible_frontend_search.levenshtein.max', $config['levenshtein']['max']);
        $container->setParameter('phlexible_frontend_search.solr.partial.enable', $config['solr']['partial']['enable']);
        $container->setParameter('phlexible_frontend_search.solr.partial.boost', $config['solr']['partial']['boost']);
        $container->setParameter('phlexible_frontend_search.solr.fuzzy.enable', $config['solr']['fuzzy']['enable']);
        $container->setParameter('phlexible_frontend_search.solr.fuzzy.boost', $config['solr']['fuzzy']['boost']);
        $container->setParameter('phlexible_frontend_search.solr.fuzzy.sim', $config['solr']['fuzzy']['sim']);
        $container->setParameter('phlexible_frontend_search.solr.fuzzy.min_length', $config['solr']['fuzzy']['min_length']);
        $container->setParameter('phlexible_frontend_search.solr.subquery.size', $config['solr']['subquery']['size']);
        $container->setParameter('phlexible_frontend_search.solr.subquery.size_restricted', $config['solr']['subquery']['size_restricted']);
        $container->setParameter('phlexible_frontend_search.solr.result.suggestions', $config['solr']['result']['suggestions']);

        $container->setAlias('phlexible_frontend_search.cache', 'phlexible_cache.managed_cache');
    }
}
