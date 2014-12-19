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
        $result = $parser->parse($queryString);

        $parts = array();
        $hasPhrase = false;
        $hasTerm = false;
        foreach ($result->all() as $part) {
            $parts[$part['occurrence']][$part['text']] = $part['type'];
            if ($part['type'] === ParseResult::PHRASE) {
                $hasPhrase = true;
            } else {
                $hasTerm = true;
            }
        }
print_r($parts);
        if (empty($parts[ParseResult::MUST]) && empty($parts[ParseResult::MUST_NOT]) && !$hasPhrase) {
            // only shoulds and no phrases, simple query string
            $query = new QueryString($queryString);
        } else {
            // only terms, bool + match
            $query = new DisMaxQuery();
            foreach ($fields as $field => $boost) {
                $boolQuery = new BoolQuery();
                foreach ($parts as $occurance => $texts) {
                    $matchQuery = new MatchQuery();
                    foreach ($texts as $text => $type) {
                        echo $text.PHP_EOL;
                        $matchQuery
                            ->setFieldQuery($field, $text)
                            ->setFieldBoost($field, $boost);
                        if ($type === 'phrase') {
                            $matchQuery->setFieldType($field, 'phrase');
                        }
                    }
                    $method = 'add' . ucfirst($occurance);
                    echo "$method()".PHP_EOL;
                    $boolQuery->$method($matchQuery);
                }
                //print_r($boolQuery);
                die;
                $query->addQuery($boolQuery);
            }
        }

        return $query;
    }
}
