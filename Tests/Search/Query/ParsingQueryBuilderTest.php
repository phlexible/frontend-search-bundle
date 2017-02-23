<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Elastica\Query\BoolQuery;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper\QueryStringEscaperInterface;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\ParsingQueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * Parsing query builder test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\FrontendSearchBundle\Search\Query\ParsingQueryBuilder
 */
class ParsingQueryBuilderTest extends TestCase
{
    /**
     * @var ParsingQueryBuilder
     */
    private $builder;

    public function setUp()
    {
        $escaper = $this->prophesize(QueryStringEscaperInterface::class);
        $escaper->escapeQueryString(Argument::type('string'))->willReturnArgument(0);

        $this->builder = new ParsingQueryBuilder($escaper->reveal());
    }

    public function testBuildPhrase()
    {
        $q = '"hello world"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf(BoolQuery::class, $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(1, $params['should']);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'hello world',
                    'type' => 'phrase',
                ),
            ),
            $matchQuery->toArray()
        );
    }

    public function testBuildBoolQuery()
    {
        $q = '+foo -bar baz';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf(BoolQuery::class, $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('must', $params);
        $this->assertArrayHasKey('must_not', $params);
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(1, $params['must']);
        $this->assertCount(1, $params['must_not']);
        $this->assertCount(1, $params['should']);
        $matchQuery = array_shift($params['must']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'foo',
                ),
            ),
            $matchQuery->toArray()
        );
        $matchQuery = array_shift($params['must_not']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'bar',
                ),
            ),
            $matchQuery->toArray()
        );
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'baz',
                ),
            ),
            $matchQuery->toArray()
        );
    }

    public function testBuildComplex()
    {
        $q = 'foo +bar -baz "sit amet"';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));

        $this->assertInstanceOf(BoolQuery::class, $query);
        $params = $query->getParams();
        $this->assertArrayHasKey('must', $params);
        $this->assertArrayHasKey('must_not', $params);
        $this->assertArrayHasKey('should', $params);
        $this->assertCount(2, $params['should']);
        $this->assertCount(1, $params['must']);
        $this->assertCount(1, $params['must_not']);
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'foo',
                ),
            ),
            $matchQuery->toArray()
        );
        $matchQuery = array_shift($params['should']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'sit amet',
                    'type' => 'phrase',
                ),
            ),
            $matchQuery->toArray()
        );
        $matchQuery = array_shift($params['must']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'bar',
                ),
            ),
            $matchQuery->toArray()
        );
        $matchQuery = array_shift($params['must_not']);
        $this->assertEquals(
            array(
                'multi_match' => array(
                    'fields' => array(
                        'title^1.2',
                        'content^1.0',
                    ),
                    'query' => 'baz',
                ),
            ),
            $matchQuery->toArray()
        );
    }
}
