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
 * Query builder interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface QueryBuilderInterface
{
    /**
     * @param string $queryString
     * @param array  $fields
     *
     * @return Query\AbstractQuery
     */
    public function build($queryString, array $fields);
}
