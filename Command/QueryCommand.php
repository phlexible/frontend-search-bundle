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

use Elastica\Facet;
use Elastica\Filter;
use Elastica\Query;
use Elastica\Suggest;
use Phlexible\Bundle\ElasticaBundle\Elastica\Index;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Query command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryCommand extends Command
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @param Index $index
     */
    public function __construct(Index $index)
    {
        parent::__construct();

        $this->index = $index;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('frontend-search:query')
            ->setDescription('Run search.')
            ->addOption('query', null, InputOption::VALUE_REQUIRED, 'Query string')
            ->addOption('facet', null, InputOption::VALUE_REQUIRED, 'Facet fields, use format "field1,field2')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED, 'Filter term, use format "field:term"')
            ->addOption('suggest', null, InputOption::VALUE_REQUIRED, 'Suggest term, use format "field:term')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = new Query();

        $queryString = $input->getOption('query');
        if ($queryString) {
            $query->setQuery(new Query\QueryString($queryString));
        }

        $filter = $input->getOption('filter');
        if ($filter) {
            list($field, $value) = explode(':', $filter);
            $filter = new Filter\Term(array($field => $value));
            $query->setPostFilter($filter);
        }

        $facet = $input->getOption('facet');
        if ($facet) {
            $facetFields = explode(',', $facet);
            $facets = array();
            foreach ($facetFields as $facetField) {
                $facet = new Facet\Terms($facetField);
                $facet->setField($facetField);
                $facets[] = $facet;
            }
            $query->setFacets($facets);
        }

        $suggest = $input->getOption('suggest');
        if ($suggest) {
            list($field, $value) = explode(':', $suggest);
            $suggestion = new Suggest\Term($field, $field);
            $suggestion->setText($value);
            $suggest = new Suggest($suggestion);
            $query->setSuggest($suggest);
        }

        $result = $this->index->search($query);

        $output->writeln("{$result->getTotalHits()} hits");
        $output->writeln("Took {$result->getTotalTime()} hits");
        $output->writeln("Max score {$result->getMaxScore()}");
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            foreach ($result->getResults() as $result) {
                var_dump($result);
            }
        }

        return 0;
    }
}
