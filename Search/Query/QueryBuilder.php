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
     * @return Query|Query\Bool
     */
    public function build($queryString, array $fields)
    {
        $queryString = str_replace('/', '\/', $queryString);

        $parser = new QueryStringParser();

        $occurrences = array();
        $hasPhrase = false;
        foreach ($parser->parse($queryString) as $term) {
            if (is_array($term->getValue())) {
                $occurrences[$term->getOccurrence()][implode(' ', $term->getValue())] = 'phrase';
                $hasPhrase = true;
            } else {
                $occurrences[$term->getOccurrence()][$term->getValue()] = 'term';
            }
        }

        if (empty($occurrences[ParseResult::MUST]) && empty($occurrences[ParseResult::MUST_NOT]) && !$hasPhrase) {
            // only shoulds and no phrases, simple query string
            $query = new Query\QueryString($queryString);
        } else {
            // only terms, bool + match

            $query = new Query\Bool();
            foreach ($occurrences as $occurance => $terms) {
                foreach ($terms as $term => $type) {
                    $matchQuery = new Query\Match();
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
