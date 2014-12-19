<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\QueryStringParser;

require_once __DIR__ . '/../../../Search/Query/ParseResult.php';
require_once __DIR__ . '/../../../Search/Query/QueryStringParser.php';

/**
 * Query string parser test
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
        $q = 'test';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'should');
        $this->assertEquals($result[0]->getValue(), 'test');
    }

    public function testParseSingleMustWord()
    {
        $q = '+test';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'must');
        $this->assertEquals($result[0]->getValue(), 'test');
    }

    public function testParseSingleMustNotWord()
    {
        $q = '-test';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'mustNot');
        $this->assertEquals($result[0]->getValue(), 'test');
    }

    public function testParseShouldPhrase()
    {
        $q = '"test phrase"';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'should');
        $this->assertEquals($result[0]->getValue(), array('test', 'phrase'));
    }

    public function testParseMustPhrase()
    {
        $q = '+"test phrase"';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'must');
        $this->assertEquals($result[0]->getValue(), array('test', 'phrase'));
    }

    public function testParseMustNotPhrase()
    {
        $q = '-"test phrase"';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'mustNot');
        $this->assertEquals($result[0]->getValue(), array('test', 'phrase'));
    }

    public function testParsePhraseWithSingleWord()
    {
        $q = '"test"';

        $result = $this->parser->parse($q);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'should');
        $this->assertEquals($result[0]->getValue(), 'test');
    }

    public function testParseShouldAndMust()
    {
        $q = 'hello +world';

        $result = $this->parser->parse($q);

        $this->assertCount(2, $result);
        $this->assertEquals($result[0]->getOccurrence(), 'should');
        $this->assertEquals($result[0]->getValue(), 'hello');
        $this->assertEquals($result[1]->getOccurrence(), 'must');
        $this->assertEquals($result[1]->getValue(), 'world');
    }

    public function testParseShouldAndShould()
    {
        $q = 'should1 should2';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'should1', 'occurrence' => 'should', 'type' => 'term'),
            array('text' => 'should2', 'occurrence' => 'should', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseShouldAndMustNot()
    {
        $q = 'should -mustNot';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'should', 'occurrence' => 'should', 'type' => 'term'),
            array('text' => 'mustNot', 'occurrence' => 'mustNot', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseShouldAndMustntAndMust()
    {
        $q = 'should -mustNot +must';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'should', 'occurrence' => 'should', 'type' => 'term'),
            array('text' => 'mustNot', 'occurrence' => 'mustNot', 'type' => 'term'),
            array('text' => 'must', 'occurrence' => 'must', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParsePhraseAndShould()
    {
        $q = '"test phrase" should';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'test phrase', 'occurrence' => 'should', 'type' => 'phrase'),
            array('text' => 'should', 'occurrence' => 'should', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseMustAndPhrase()
    {
        $q = '+must "test phrase"';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'must', 'occurrence' => 'must', 'type' => 'term'),
            array('text' => 'test phrase', 'occurrence' => 'should', 'type' => 'phrase')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseMustAndPhraseAndMustnt()
    {
        $q = '+must "test phrase" -mustNot';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'must', 'occurrence' => 'must', 'type' => 'term'),
            array('text' => 'test phrase', 'occurrence' => 'should', 'type' => 'phrase'),
            array('text' => 'mustNot', 'occurrence' => 'mustNot', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseMissingPhraseEnd()
    {
        $q = '"test phrase';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'test phrase', 'occurrence' => 'should', 'type' => 'phrase')
        );

        $this->assertEquals($expected, $result->all());
    }

    public function testParseMisplacedPhraseStart()
    {
        $q = 'should1 should2"phrase';

        $result = $this->parser->parse($q);
        $expected = array(
            array('text' => 'should1', 'occurrence' => 'should', 'type' => 'term'),
            array('text' => 'should2', 'occurrence' => 'should', 'type' => 'term'),
            array('text' => 'phrase', 'occurrence' => 'should', 'type' => 'term')
        );

        $this->assertEquals($expected, $result->all());
    }
}
