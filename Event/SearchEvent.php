<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Listener;

use Phlexible\FrontendSearchComponent\Events;
use Symfony\Component\EventDispatcher\Event;

/**
 * Search event
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class SearchEvent extends Event
{
    /**
     * @var Search
     */
    protected $search;

    /**
     * @var array
     */
    protected $results;

    /**
     * @param Search $search
     * @param Result $result
     */
    public function __construct(Search $search, Result $results)
    {
        $this->search  = $search;
        $this->results = $results;
    }

    /**
     * @return Search
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return Result
     */
    public function getResults()
    {
        return $this->results;
    }

}
