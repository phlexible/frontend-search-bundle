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
 * Parsing query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ParsingQueryBuilder implements QueryBuilderInterface
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
     * @return Query|Query\Bool
     */
    public function build($queryString, array $fields)
    {
        $escapedQueryString = $this->escaper->escapeQueryString($queryString);

        $parser = new QueryStringParser();

        $occurrences = array();
        foreach ($parser->parse($escapedQueryString) as $term) {
            if (is_array($term->getValue())) {
                $occurrences[$term->getOccurrence()][implode(' ', $term->getValue())] = 'phrase';
            } else {
                $occurrences[$term->getOccurrence()][$term->getValue()] = 'term';
            }
        }

        $query = new Query\Bool();
        foreach ($occurrences as $occurance => $terms) {
            foreach ($terms as $term => $type) {
                $matchQuery = new Query\MultiMatch();
                $boostedFields = array();
                foreach ($fields as $field => $boost) {
                    $boostedFields[] = sprintf("%s^%0.1f", $field, $boost);
                }

                $matchQuery
                    ->setFields($boostedFields)
                    ->setQuery($term);

                if ($type === 'phrase') {
                    $matchQuery->setType('phrase');
                }

                $method = 'add' . ucfirst($occurance);
                $query->$method($matchQuery);
            }
        }

        return $query;
    }
}
