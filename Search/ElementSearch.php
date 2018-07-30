<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search;

use Elastica\Aggregation;
use Elastica\Filter;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Elastica\ResultSet;
use Elastica\Suggest;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryBuilderInterface;

/**
 * Element search.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementSearch
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var QueryBuilderInterface
     */
    private $queryBuilder;

    /**
     * @param Index                 $index
     * @param QueryBuilderInterface $queryBuilder
     */
    public function __construct(Index $index, QueryBuilderInterface $queryBuilder)
    {
        $this->index = $index;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $queryString
     * @param string $language
     * @param string $siterootId
     * @param int    $limit
     * @param int    $start
     *
     * @return ResultSet
     */
    public function search($queryString, $language, $siterootId, $limit, $start = 0)
    {
        $filter = new BoolQuery();

        if ($siterootId) {
            $filter->addMust(new Term(array('siteroot_id' => $siterootId)));
        }

        if ($language) {
            $filter->addMust(new Term(array('language' => $language)));
        }

        $query = new Query();
        $query
            ->setFrom($start)
            ->setSize($limit)
            ->setHighlight(
                array(
                    'fields' => array(
                        'title' => array('fragment_size' => 20, 'number_of_fragments' => 1),
                        'content' => array('fragment_size' => 400, 'number_of_fragments' => 2),
                    ),
                )
            )
            ->setPostFilter($filter)
            ->setQuery($this->queryBuilder->build($queryString, array('title' => 1.2, 'content' => 1.0)));

        return $this->index->search($query);
    }

    /**
     * @param string $queryString
     * @param string $language
     * @param string $siterootId
     *
     * @return array
     */
    public function suggest($queryString, $language, $siterootId)
    {
        $suggest = new Suggest();
        $term = new Suggest\Term('suggest', '_all');
        $term->setText($queryString);
        $suggest->addSuggestion($term);

        $filter = new BoolQuery();
        $filter->addMust(new Term(['language' => $language]));
        $filter->addMust(new Term(['siteroot_id' => $siterootId]));

        $query = new Query();
        $query->setSuggest($suggest);
        $query->setPostFilter($filter);

        $results = $this->index->search($query);

        $suggestions = array();
        if (!empty($results->getSuggests())) {
            $suggestions = $results->getSuggests()['suggest'][0]['options'];
        }

        return $suggestions;
    }

    /**
     * @param string $queryString
     * @param string $language
     * @param string $siterootId
     *
     * @return array
     */
    public function autocomplete($queryString, $language, $siterootId)
    {
        $filter = new BoolQuery();

        if ($siterootId) {
            $filter->addMust(new Term(array('siteroot_id' => $siterootId)));
        }

        if ($language) {
            $filter->addMust(new Term(array('language' => $language)));
        }

        $aggregation = new Aggregation\Terms('autocomplete');
        $aggregation
            ->setField('autocomplete')
            ->setOrder('_count', 'desc')
            ->setInclude("$queryString.*", '');

        $query = new Query();
        $query
            ->setSize(0)
            ->setQuery(new Query\Prefix(array('autocomplete' => $queryString)))
            ->setPostFilter($filter)
            ->addAggregation($aggregation);

        $results = $this->index->search($query);

        $autocompletes = array();
        if (!empty($results->getAggregation('autocomplete')['buckets'])) {
            foreach ($results->getAggregation('autocomplete')['buckets'] as $bucket) {
                $autocompletes[] = array(
                    'value' => $bucket['key'],
                    'count' => $bucket['doc_count'],
                );
            }
        }

        return $autocompletes;
    }
}
