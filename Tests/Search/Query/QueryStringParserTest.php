<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringParser;

/**
 * Query string parser test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryStringParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryStringParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new QueryStringParser();
    }

    public function testParseSingleShouldWord()
    {
        $q = 'foo';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParseSingleMustWord()
    {
        $q = '+foo';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('must', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParseSingleMustNotWord()
    {
        $q = '-foo';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('mustNot', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParseShouldPhrase()
    {
        $q = '"hello world"';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
    }

    public function testParseMustPhrase()
    {
        $q = '+"hello world"';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('must', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
    }

    public function testParseMustNotPhrase()
    {
        $q = '-"hello world"';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('mustNot', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
    }

    public function testParsePhraseWithSingleWord()
    {
        $q = '"foo"';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParseShouldAndMust()
    {
        $q = 'hello +world';

        $terms = $this->parser->parse($q);

        $this->assertCount(2, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('hello', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('must', $term->getOccurrence());
        $this->assertEquals('world', $term->getValue());
    }

    public function testParseShouldAndShould()
    {
        $q = 'hello world';

        $terms = $this->parser->parse($q);

        $this->assertCount(2, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('hello', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('world', $term->getValue());
    }

    public function testParseShouldAndMustNot()
    {
        $q = 'hello -world';

        $terms = $this->parser->parse($q);

        $this->assertCount(2, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('hello', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('mustNot', $term->getOccurrence());
        $this->assertEquals('world', $term->getValue());
    }

    public function testParseShouldAndMustntAndMust()
    {
        $q = 'hello -world +foo';

        $terms = $this->parser->parse($q);

        $this->assertCount(3, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('hello', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('mustNot', $term->getOccurrence());
        $this->assertEquals('world', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('must', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParsePhraseAndShould()
    {
        $q = '"hello world" foo';

        $terms = $this->parser->parse($q);

        $this->assertCount(2, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
    }

    public function testParseMustAndPhrase()
    {
        $q = '+foo "hello world"';

        $terms = $this->parser->parse($q);

        $this->assertCount(2, $terms);
        $term = array_shift($terms);
        $this->assertEquals($term->getOccurrence(), 'must');
        $this->assertEquals($term->getValue(), 'foo');
        $term = array_shift($terms);
        $this->assertEquals($term->getOccurrence(), 'should');
        $this->assertEquals($term->getValue(), array('hello', 'world'));
    }

    public function testParseMustAndPhraseAndMustnt()
    {
        $q = '+foo "hello world" -bar';

        $terms = $this->parser->parse($q);

        $this->assertCount(3, $terms);
        $term = array_shift($terms);
        $this->assertEquals('must', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('mustNot', $term->getOccurrence());
        $this->assertEquals('bar', $term->getValue());
    }

    public function testParseMissingPhraseEnd()
    {
        $q = '"hello world';

        $terms = $this->parser->parse($q);

        $this->assertCount(1, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
    }

    public function testParseMisplacedPhraseTermStart()
    {
        $q = 'foo bar"baz';

        $terms = $this->parser->parse($q);

        $this->assertCount(3, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('bar', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('baz', $term->getValue());
    }

    public function testParseMisplacedPhraseStart()
    {
        $q = 'foo bar"hello world';

        $terms = $this->parser->parse($q);

        $this->assertCount(3, $terms);
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('foo', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals('bar', $term->getValue());
        $term = array_shift($terms);
        $this->assertEquals('should', $term->getOccurrence());
        $this->assertEquals(array('hello', 'world'), $term->getValue());
    }
}
