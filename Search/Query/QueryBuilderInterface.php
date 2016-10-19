<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
