<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search;

use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\ResultSet;
use Phlexible\Bundle\ElasticaBundle\Elastica\Index;
use Phlexible\Bundle\FrontendSearchBundle\Search\ElementSearch;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryBuilderInterface;
use Prophecy\Argument;

/**
 * Element search test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementSearchTest extends \PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        $index = $this->prophesize(Index::class);
        $queryBuilder = $this->prophesize(QueryBuilderInterface::class);
        $query = new QueryString('hello world');
        $queryBuilder->build('hello world', array('title' => 1.2, 'content' => 1.0))->willReturn($query);

        $index->search(Argument::that(function(Query $receivedQuery) use ($query) {
            $this->assertSame(5, $receivedQuery->getParam('size'));
            $this->assertSame(10, $receivedQuery->getParam('from'));
            $this->assertSame(array('fields' => array('title' => array('fragment_size' => 20, 'number_of_fragments' => 1), 'content' => array('fragment_size' => 400, 'number_of_fragments' => 2))), $receivedQuery->getParam('highlight'));
            $this->assertSame(array('and' => array(array('term' => array('siterootId' => 'abc')), array('term' => array('language' => 'de')))), $receivedQuery->getParam('post_filter')->toArray());
            $this->assertSame(array('query_string' => array('query' => 'hello world')), $receivedQuery->getParam('query')->toArray());

            return true;
        }))->willReturn($this->prophesize(ResultSet::class)->reveal());

        $search = new ElementSearch($index->reveal(), $queryBuilder->reveal());
        $search->search('hello world', 'de', 'abc', 5, 10);
    }

    public function testSuggest()
    {
        $index = $this->prophesize(Index::class);
        $queryBuilder = $this->prophesize(QueryBuilderInterface::class);
        $query = new QueryString('hello world');
        $queryBuilder->build('hello world', array('title' => 1.2, 'content' => 1.0))->willReturn($query);

        $index->search(Argument::that(function(Query $receivedQuery) use ($query) {
            $this->assertSame(array('and' => array(array('term' => array('siterootId' => 'abc')), array('term' => array('language' => 'de')))), $receivedQuery->getParam('post_filter')->toArray());
            $this->assertSame(array('multi_match' => array('query' => 'hello world', 'fields' => array('title', 'content'))), $receivedQuery->getParam('query')->toArray());

            return true;
        }))->shouldBeCalled()->willReturn($this->prophesize(ResultSet::class)->reveal());

        $search = new ElementSearch($index->reveal(), $queryBuilder->reveal());
        $search->suggest('hello world', 'de', 'abc');
    }

    public function testAutocomplete()
    {
        $index = $this->prophesize(Index::class);
        $queryBuilder = $this->prophesize(QueryBuilderInterface::class);
        $query = new QueryString('hello');
        $queryBuilder->build('hello', array('title' => 1.2, 'content' => 1.0))->willReturn($query);

        $index->search(Argument::that(function(Query $receivedQuery) use ($query) {
            $this->assertSame(array('and' => array(array('term' => array('siterootId' => 'abc')), array('term' => array('language' => 'de')))), $receivedQuery->getParam('post_filter')->toArray());
            $this->assertSame(array('prefix' => array('autocomplete' => 'hello')), $receivedQuery->getParam('query')->toArray());
            $this->assertSame(array('terms' => array('field' => 'autocomplete', 'order' => array('_count' => 'desc'), 'include' => array('pattern' => 'hello.*', 'flags' => ''))), $receivedQuery->getParam('aggs')[0]->toArray());

            return true;
        }))->shouldBeCalled()->willReturn($this->prophesize(ResultSet::class)->reveal());

        $search = new ElementSearch($index->reveal(), $queryBuilder->reveal());
        $search->autocomplete('hello', 'de', 'abc');
    }
}
