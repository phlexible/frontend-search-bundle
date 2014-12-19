<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryBuilder;

require_once __DIR__ . '/../../../Search/Query/ParseResult.php';
require_once __DIR__ . '/../../../Search/Query/QueryStringParser.php';
require_once __DIR__ . '/../../../Search/Query/QueryBuilder.php';

/**
 * Query builder test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new QueryBuilder();
    }

    public function testBuildQueryString()
    {
        $q = 'query string';

        $query = $this->builder->build($q, array('title', 'content'));
        $expected = array(
            'query' => 'query string'
        );

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\QueryString', $query);
        $this->assertEquals($expected, $query->getParams());
    }

    public function testBuildPhrase()
    {
        $q = '"test phrase"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));
        $expected = array(
            'title' => array(
                'type' => 'phrase',
                'boost' => 1.2,
                'query' => $q,
            ),
            'content' => array(
                'type' => 'phrase',
                'boost' => 1.0,
                'query' => $q,
            )
        );

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\DisMaxQuery', $query);
        $boolQueries = $query->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[1]);
        $matchQueries = $boolQueries[0]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('title' => array('query' => 'test phrase', 'boost' => 1.2, 'type' => 'phrase')), $matchQueries['should'][0]->getFields());
        $matchQueries = $boolQueries[1]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('content' => array('query' => 'test phrase', 'boost' => 1.0, 'type' => 'phrase')), $matchQueries['should'][0]->getFields());
    }

    public function testBuildBool()
    {
        $q = '+bool -query string';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\DisMaxQuery', $query);
        $boolQueries = $query->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[1]);
        $matchQueries = $boolQueries[0]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['must'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['mustNot'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('title' => array('query' => 'bool', 'boost' => 1.2)), $matchQueries['must'][0]->getFields());
        $this->assertEquals(array('title' => array('query' => 'query', 'boost' => 1.2)), $matchQueries['mustNot'][0]->getFields());
        $this->assertEquals(array('title' => array('query' => 'string', 'boost' => 1.2)), $matchQueries['should'][0]->getFields());
        $matchQueries = $boolQueries[1]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['must'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['mustNot'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('content' => array('query' => 'bool', 'boost' => 1.0)), $matchQueries['must'][0]->getFields());
        $this->assertEquals(array('content' => array('query' => 'query', 'boost' => 1.0)), $matchQueries['mustNot'][0]->getFields());
        $this->assertEquals(array('content' => array('query' => 'string', 'boost' => 1.0)), $matchQueries['should'][0]->getFields());
    }

    public function testBuildComplex()
    {
        $q = 'lorem +ipsum -dolor "sit amet"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));
        $expected = array(
        );

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\DisMaxQuery', $query);
        $boolQueries = $query->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $boolQueries[1]);
        $matchQueries = $boolQueries[0]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['must'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['mustNot'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('title' => array('query' => 'ipsum', 'boost' => 1.2)), $matchQueries['must'][0]->getFields());
        $this->assertEquals(array('title' => array('query' => 'dolor', 'boost' => 1.2)), $matchQueries['mustNot'][0]->getFields());
        //$this->assertEquals(array('title' => array('query' => 'lorem', 'boost' => 1.2)), $matchQueries['should'][0]->getFields());
        $this->assertEquals(array('title' => array('query' => 'sit amet', 'boost' => 1.2, 'type' => 'phrase')), $matchQueries['should'][0]->getFields());
        $matchQueries = $boolQueries[1]->getQueries();
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['must'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['mustNot'][0]);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQueries['should'][0]);
        $this->assertEquals(array('content' => array('query' => 'ipsum', 'boost' => 1.0)), $matchQueries['must'][0]->getFields());
        $this->assertEquals(array('content' => array('query' => 'dolor', 'boost' => 1.0)), $matchQueries['mustNot'][0]->getFields());
        $this->assertEquals(array('content' => array('query' => 'lorem', 'boost' => 1.0)), $matchQueries['should'][1]->getFields());
        $this->assertEquals(array('content' => array('query' => 'sit amet', 'boost' => 1.0, 'type' => 'phrase')), $matchQueries['should'][0]->getFields());
    }
}
