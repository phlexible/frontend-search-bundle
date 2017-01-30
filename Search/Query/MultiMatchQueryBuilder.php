<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

use Elastica\Query;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper\QueryStringEscaperInterface;

/**
 * Multi match query builder.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MultiMatchQueryBuilder implements QueryBuilderInterface
{
    /**
     * @var QueryStringEscaperInterface
     */
    private $escaper;

    /**
     * MultiMatchQueryBuilder constructor.
     *
     * @param QueryStringEscaperInterface $escaper
     */
    public function __construct(QueryStringEscaperInterface $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @param string $queryString
     * @param array  $fields
     *
     * @return Query\MultiMatch
     */
    public function build($queryString, array $fields)
    {
        $escapedQueryString = $this->escaper->escapeQueryString($queryString);

        $boostedFields = array();
        foreach ($fields as $field => $boost) {
            $boostedFields[] = sprintf('%s^%0.1f', $field, $boost);
        }

        $query = new Query\MultiMatch();
        $query->setFields($boostedFields);
        $query->setQuery($escapedQueryString);

        return $query;
    }
}
