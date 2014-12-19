<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryBuilder;
use Phlexible\Bundle\IndexerBundle\Query\Aggregation\TermsAggregation;
use Phlexible\Bundle\IndexerBundle\Query\Filter\BoolAndFilter;
use Phlexible\Bundle\IndexerBundle\Query\Filter\TermFilter;
use Phlexible\Bundle\IndexerBundle\Query\Query\MultiMatchQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\PrefixQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\QueryString;
use Phlexible\Bundle\IndexerBundle\Query\Suggest;
use Phlexible\Bundle\IndexerBundle\Query\Suggest\TermSuggest;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;

/**
 * Element search
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementSearch
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $queryString
     * @param string $language
     * @param string $siterootId
     * @param int    $limit
     * @param int    $start
     *
     * @return array
     */
    public function search($queryString, $language, $siterootId, $limit, $start = 0)
    {
        $filter = new BoolAndFilter();
        $filter
            //->addFilter(new TermFilter(array('siterootId' => $siterootId)))
            ->addFilter(new TermFilter(array('language' => $language)));

        $queryBuilder = new QueryBuilder();

        $query = $this->storage->createQuery()
            ->setStart($start)
            ->setSize($limit)
            ->setHighlight(
                array(
                    'fields' => array(
                        'title' => array('fragment_size' => 20, 'number_of_fragments' => 1),
                        'content' => array('fragment_size' => 400, 'number_of_fragments' => 2)
                    )
                )
            )
            ->setFilter($filter)
            ->setQuery($queryBuilder->build($queryString, array('title' => 1.2, 'content' => 1.0)));

        return $this->storage->query($query);
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
        $suggestion = new TermSuggest('didYouMean', 'did_you_mean');
        $suggestions = new Suggest($suggestion);
        $suggestions->setGlobalText($queryString);

        $filter = new BoolAndFilter();
        $filter
        //->addFilter(new TermFilter(array('siterootId' => $siterootId)))
            ->addFilter(new TermFilter(array('language' => $language)));

        $query = new MultiMatchQuery();
        $query
            ->setQuery($queryString)
            ->setFields(array('title', 'content'));

        $q = $this->storage->createQuery()
            ->setQuery($query)
            ->setFilter($filter)
            ->setSuggest($suggestions);

        $results = $this->storage->query($q);

        $suggestions = array();
        if (!empty($results['suggest']['didYouMean'][0]['options'])) {
            $suggestions = $results['suggest']['didYouMean'][0]['options'];
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
        $filter = new BoolAndFilter();
        $filter
            //->addFilter(new TermFilter(array('siterootId' => $siterootId)))
            ->addFilter(new TermFilter(array('language' => $language)));

        $aggregation = new TermsAggregation('autocomplete');
        $aggregation
            ->setField('autocomplete')
            ->setOrder('_count', 'desc')
            ->setInclude("$queryString.*", '');

        $query = $this->storage->createQuery()
            ->setSize(0)
            ->setQuery(new PrefixQuery(array('autocomplete' => $queryString)))
            ->setFilter($filter)
            ->addAggregation($aggregation);

        $results = $this->storage->query($query);

        $autocompletes = array();
        foreach ($results['aggregations']['autocomplete']['buckets'] as $bucket) {
            $autocompletes[] = array(
                'value' => $bucket['key'],
                'count' => $bucket['doc_count']
            );
        }

        return $autocompletes;
    }
}
