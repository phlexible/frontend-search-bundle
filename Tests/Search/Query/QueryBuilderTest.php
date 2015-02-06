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

        $this->assertInstanceOf('Elastica\Query\QueryString', $query);
        $this->assertEquals($expected, $query->getParams());
    }

    public function testBuildPhrase()
    {
        $q = '"hello world"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Elastica\Query\Bool', $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(1, $params['should']);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'hello world', 'boost' => 1.2, 'type' => 'phrase'), 'content' => array('query' => 'hello world', 'boost' => 1.0, 'type' => 'phrase'))), $matchQuery);
    }

    public function testBuildBool()
    {
        $q = '+foo -bar baz';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Elastica\Query\Bool', $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('must', $params);
        $this->assertArrayHasKey('must_not', $params);
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(1, $params['must']);
        $this->assertCount(1, $params['must_not']);
        $this->assertCount(1, $params['should']);
        $matchQuery = array_shift($params['must']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'foo', 'boost' => 1.2), 'content' => array('query' => 'foo', 'boost' => 1.0))), $matchQuery);
        $matchQuery = array_shift($params['must_not']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'bar', 'boost' => 1.2), 'content' => array('query' => 'bar', 'boost' => 1.0))), $matchQuery);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'baz', 'boost' => 1.2), 'content' => array('query' => 'baz', 'boost' => 1.0))), $matchQuery);
    }

    public function testBuildComplex()
    {
        $q = 'foo +bar -baz "sit amet"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf('Elastica\Query\Bool', $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('must', $params);
        $this->assertArrayHasKey('must_not', $params);
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(2, $params['should']);
        $this->assertCount(1, $params['must']);
        $this->assertCount(1, $params['must_not']);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'foo', 'boost' => 1.2), 'content' => array('query' => 'foo', 'boost' => 1.0))), $matchQuery);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'sit amet', 'boost' => 1.2, 'type' => 'phrase'), 'content' => array('query' => 'sit amet', 'boost' => 1.0, 'type' => 'phrase'))), $matchQuery);
        $matchQuery = array_shift($params['must']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'bar', 'boost' => 1.2), 'content' => array('query' => 'bar', 'boost' => 1.0))), $matchQuery);
        $matchQuery = array_shift($params['must_not']);
        $this->assertEquals(array('match' => array('title' => array('query' => 'baz', 'boost' => 1.2), 'content' => array('query' => 'baz', 'boost' => 1.0))), $matchQuery);
    }
}
