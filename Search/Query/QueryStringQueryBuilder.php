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
 * Query string query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryStringQueryBuilder implements QueryBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build($queryString, array $fields)
    {
        $queryString = str_replace('/', '\/', $queryString);
        $queryString = str_replace(':', '\:', $queryString);

        $query = new Query\QueryString($queryString);

        return $query;
    }
}
