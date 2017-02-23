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

use Elastica\Query\QueryString;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper\QueryStringEscaperInterface;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringQueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * Query builder test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringQueryBuilder
 */
class QueryStringQueryBuilderTest extends TestCase
{
    /**
     * @var QueryStringQueryBuilder
     */
    private $builder;

    public function setUp()
    {
        $escaper = $this->prophesize(QueryStringEscaperInterface::class);
        $escaper->escapeQueryString(Argument::type('string'))->willReturnArgument(0);

        $this->builder = new QueryStringQueryBuilder($escaper->reveal());
    }

    public function testBuildQueryString()
    {
        $q = 'hello world';

        $query = $this->builder->build($q, array('title', 'content'));
        $expected = array(
            'query' => 'hello world',
        );

        $this->assertInstanceOf(QueryString::class, $query);
        $this->assertEquals($expected, $query->getParams());
    }
}
