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
     * @param string $queryString
     * @param array  $fields
     *
     * @return Query|Query\Bool
     */
    public function build($queryString, array $fields)
    {
        $queryString = str_replace('/', '\/', $queryString);
        $queryString = str_replace(':', '\:', $queryString);

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
