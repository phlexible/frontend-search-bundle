<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Elastica\Query\MultiMatch;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\MultiMatchQueryBuilder;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringEscaperInterface;
use Prophecy\Argument;

/**
 * Multi match query builder test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MultiMatchQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MultiMatchQueryBuilder
     */
    private $builder;

    public function setUp()
    {
        $escaper = $this->prophesize(QueryStringEscaperInterface::class);
        $escaper->escapeQueryString(Argument::type('string'))->willReturnArgument(0);

        $this->builder = new MultiMatchQueryBuilder($escaper->reveal());
    }

    public function testBuildQueryString()
    {
        $q = 'hello world';

        $query = $this->builder->build($q, array('title' => 1.2, 'content' => 1.0));
        $expected = array(
            'query' => 'hello world',
            'fields' => array('title^1.2', 'content^1.0')
        );

        $this->assertInstanceOf(MultiMatch::class, $query);
        $this->assertEquals($expected, $query->getParams());
    }
}
