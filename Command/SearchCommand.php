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
 * Search command
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class SearchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('frontend-search:search')
            ->setDescription('Run search query.')
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

        $result = $elementSearch->search($queryString, 'de', '', 20, 0);

        $output->writeln("Found {$result->getTotalHits()} hits");
        $output->writeln("Took {$result->getTotalTime()} s");
        $output->writeln("Max score {$result->getMaxScore()}");
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("Hits:");
            foreach ($result->getResults() as $result) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                    ld($result);
                } else {
                    $output->writeln("  {$result->getId()} ({$result->getScore()})");
                    if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                        foreach ($result->getHighlights() as $field => $highlight) {
                            $output->writeln("    $field: {$highlight[0]}");
                        }
                    }
                }
            }
        }

        return 0;
    }

}
