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
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);

        $container->setParameter('phlexible_frontend_search.suggestions.size', $config['suggestions']['size']);
        $container->setParameter('phlexible_frontend_search.pager.template', $config['pager']['template']);
        $container->setParameter('phlexible_frontend_search.results.default_search_route_name', $config['results']['default_search_route_name']);
        $container->setParameter('phlexible_frontend_search.results.template', $config['results']['template']);
    }
}
