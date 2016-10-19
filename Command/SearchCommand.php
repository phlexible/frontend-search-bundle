<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Search command.
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
            ->addOption('siterootId', null, InputOption::VALUE_REQUIRED)
            ->addOption('language', null, InputOption::VALUE_REQUIRED, '', 'de')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryString = strtolower(trim($input->getArgument('query')));
        $language = $input->getOption('language');
        $siterootId = $input->getOption('siterootId');

        $elementSearch = $this->getContainer()->get('phlexible_frontend_search.element_search');

        $result = $elementSearch->search($queryString, $language, $siterootId, 20, 0);

        $output->writeln("Found {$result->getTotalHits()} hits");
        $output->writeln("Took {$result->getTotalTime()} s");
        $output->writeln("Max score {$result->getMaxScore()}");
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln('Hits:');
            foreach ($result->getResults() as $result) {
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                    var_dump($result);
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
