<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Command;

use Elastica\Facet;
use Elastica\Filter;
use Elastica\Query;
use Elastica\Suggest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Complete command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class CompleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('frontend-search:complete')
            ->setDescription('Run complete query.')
            ->addArgument('query', InputArgument::REQUIRED, 'Query string')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryString = strtolower(trim($input->getArgument('query')));

        $elementSearch = $this->getContainer()->get('phlexible_frontend_search.element_search');

        $autocompletes = $elementSearch->autocomplete($queryString, 'de', '');

        ld($autocompletes);

        return 0;
    }

}
