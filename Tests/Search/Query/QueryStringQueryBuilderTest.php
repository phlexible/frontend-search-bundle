<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Elastica\Query\QueryString;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringEscaperInterface;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringQueryBuilder;
use Prophecy\Argument;

/**
 * Query builder test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryStringQueryBuilderTest extends \PHPUnit_Framework_TestCase
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
            'query' => 'hello world'
        );

        $this->assertInstanceOf(QueryString::class, $query);
        $this->assertEquals($expected, $query->getParams());
    }
}
