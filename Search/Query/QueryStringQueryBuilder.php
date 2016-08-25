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
