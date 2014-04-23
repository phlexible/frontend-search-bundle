<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Listener;

use Symfony\Component\EventDispatcher\Event;

/**
 * Before search event
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class BeforeSearchEvent extends Event
{
    /**
     * @var Search
     */
    protected $search;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var string
     */
    protected $queryString;

    /**
     * @param Search $search
     * @param Query  $query
     * @param string $queryString
     */
    public function __construct(Search $search,
                                Query $query,
                                $queryString)
    {
        $this->search      = $search;
        $this->query       = $query;
        $this->queryString = $queryString;
    }

    /**
     * @return Search
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }
}
