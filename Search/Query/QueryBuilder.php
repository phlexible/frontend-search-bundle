<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

use Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\DisMaxQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\QueryInterface;
use Phlexible\Bundle\IndexerBundle\Query\Query\QueryString;

/**
 * Query builder
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryBuilder
{
    /**
     * @param string $queryString
     * @param array  $fields
     *
     * @return QueryInterface
     */
    public function build($queryString, array $fields)
    {
        $parser = new QueryStringParser();

        $occurrences = array();
        $hasPhrase = false;
        $hasTerm = false;
        foreach ($parser->parse($queryString) as $term) {
            if (is_array($term->getValue())) {
                $occurrences[$term->getOccurrence()][implode(' ', $term->getValue())] = 'phrase';
                $hasPhrase = true;
            } else {
                $occurrences[$term->getOccurrence()][$term->getValue()] = 'term';
                $hasTerm = true;
            }
        }

        if (empty($occurrences[ParseResult::MUST]) && empty($occurrences[ParseResult::MUST_NOT]) && !$hasPhrase) {
            // only shoulds and no phrases, simple query string
            $query = new QueryString($queryString);
        } else {
            // only terms, bool + match

            $query = new BoolQuery();
            foreach ($occurrences as $occurance => $terms) {
                foreach ($terms as $term => $type) {
                    $matchQuery = new MatchQuery();
                    foreach ($fields as $field => $boost) {
                        $matchQuery
                            ->setFieldQuery($field, $term)
                            ->setFieldBoost($field, $boost);
                        if ($type === 'phrase') {
                            $matchQuery->setFieldType($field, 'phrase');
                        }
                    }
                    $method = 'add' . ucfirst($occurance);
                    $query->$method($matchQuery);
                }
            }
        }

        return $query;
    }
}
