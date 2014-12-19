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
        $q = 'hello world';

        $query = $this->builder->build($q, array('title', 'content'));
        $expected = array(
            'query' => 'hello world'
        );

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\QueryString', $query);
        $this->assertEquals($expected, $query->getParams());
    }

    public function testBuildPhrase()
    {
        $q = '"hello world"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $query);
        $matchQueries = $query->getQueries();
        $this->assertArrayHasKey('should', $matchQueries);
        $this->assertCount(1, $matchQueries['should']);
        $matchQuery = array_shift($matchQueries['should']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'hello world', 'boost' => 1.2, 'type' => 'phrase'), 'content' => array('query' => 'hello world', 'boost' => 1.0, 'type' => 'phrase')), $matchQuery->getFields());
    }

    public function testBuildBool()
    {
        $q = '+foo -bar baz';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $query);
        $matchQueries = $query->getQueries();
        $this->assertArrayHasKey('must', $matchQueries);
        $this->assertArrayHasKey('mustNot', $matchQueries);
        $this->assertArrayHasKey('should', $matchQueries);
        $this->assertCount(1, $matchQueries['must']);
        $this->assertCount(1, $matchQueries['mustNot']);
        $this->assertCount(1, $matchQueries['should']);
        $matchQuery = array_shift($matchQueries['must']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'foo', 'boost' => 1.2), 'content' => array('query' => 'foo', 'boost' => 1.0)), $matchQuery->getFields());
        $matchQuery = array_shift($matchQueries['mustNot']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'bar', 'boost' => 1.2), 'content' => array('query' => 'bar', 'boost' => 1.0)), $matchQuery->getFields());
        $matchQuery = array_shift($matchQueries['should']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'baz', 'boost' => 1.2), 'content' => array('query' => 'baz', 'boost' => 1.0)), $matchQuery->getFields());
    }

    public function testBuildComplex()
    {
        $q = 'foo +bar -baz "sit amet"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\BoolQuery', $query);
        $matchQueries = $query->getQueries();
        $this->assertArrayHasKey('must', $matchQueries);
        $this->assertArrayHasKey('mustNot', $matchQueries);
        $this->assertArrayHasKey('should', $matchQueries);
        $this->assertCount(2, $matchQueries['should']);
        $this->assertCount(1, $matchQueries['must']);
        $this->assertCount(1, $matchQueries['mustNot']);
        $matchQuery = array_shift($matchQueries['should']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'foo', 'boost' => 1.2), 'content' => array('query' => 'foo', 'boost' => 1.0)), $matchQuery->getFields());
        $matchQuery = array_shift($matchQueries['should']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'sit amet', 'boost' => 1.2, 'type' => 'phrase'), 'content' => array('query' => 'sit amet', 'boost' => 1.0, 'type' => 'phrase')), $matchQuery->getFields());
        $matchQuery = array_shift($matchQueries['must']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'bar', 'boost' => 1.2), 'content' => array('query' => 'bar', 'boost' => 1.0)), $matchQuery->getFields());
        $matchQuery = array_shift($matchQueries['mustNot']);
        $this->assertInstanceOf('Phlexible\Bundle\IndexerBundle\Query\Query\MatchQuery', $matchQuery);
        $this->assertEquals(array('title' => array('query' => 'baz', 'boost' => 1.2), 'content' => array('query' => 'baz', 'boost' => 1.0)), $matchQuery->getFields());
    }
}
