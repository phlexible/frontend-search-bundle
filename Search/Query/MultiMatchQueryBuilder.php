<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

use Elastica\Query;

/**
 * Multi match query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MultiMatchQueryBuilder implements QueryBuilderInterface
{
    /**
     * @param string $queryString
     * @param array  $fields
     *
     * @return Query|Query\Bool
     */
    public function build($queryString, array $fields)
    {
        $escapedQueryString = Util::escapeQuery($queryString);

        $boostedFields = array();
        foreach ($fields as $field => $boost) {
            $boostedFields[] = sprintf("%s^%0.1f", $field, $boost);
        }

        $query = new Query\MultiMatch();
        $query->setFields($boostedFields);
        $query->setQuery($escapedQueryString);

        return $query;
    }
}
