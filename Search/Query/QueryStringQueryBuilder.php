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
 * Query string query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryStringQueryBuilder implements QueryBuilderInterface
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
     * {@inheritdoc}
     */
    public function build($queryString, array $fields)
    {
        $escapedQueryString = $this->escaper->escapeQueryString($queryString);

        $query = new Query\QueryString($escapedQueryString);

        return $query;
    }
}
