<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search;

use Phlexible\Bundle\FrontendSearchBundle\Search\QueryStringParser;

include __DIR__ . '/../../Search/QueryStringParser.php';


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

        $this->assertEquals(array(array('word' => 'test', 'type' => 'should')), $result);
    }

    public function testParseSingleMustWord()
    {
        $q = '+test';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'test', 'type' => 'must')), $result);
    }

    public function testParseSingleMustntWord()
    {
        $q = '-test';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'test', 'type' => 'mustnt')), $result);
    }

    public function testParseSinglePhrase()
    {
        $q = '"test phrase"';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'test phrase', 'type' => 'phrase')), $result);
    }

    public function testParseShouldAndMust()
    {
        $q = 'should +must';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'should', 'type' => 'should'), array('word' => 'must', 'type' => 'must')), $result);
    }

    public function testParseShouldAndShould()
    {
        $q = 'should1 should2';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'should1', 'type' => 'should'), array('word' => 'should2', 'type' => 'should')), $result);
    }

    public function testParseShouldAndMustnt()
    {
        $q = 'should -mustnt';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'should', 'type' => 'should'), array('word' => 'mustnt', 'type' => 'mustnt')), $result);
    }

    public function testParseShouldAndMustntAndMust()
    {
        $q = 'should -mustnt +must';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'should', 'type' => 'should'), array('word' => 'mustnt', 'type' => 'mustnt'), array('word' => 'must', 'type' => 'must')), $result);
    }

    public function testParsePhraseAndShould()
    {
        $q = '"test phrase"  should';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'test phrase', 'type' => 'phrase'), array('word' => 'should', 'type' => 'should')), $result);
    }

    public function testParseMustAndPhrase()
    {
        $q = '+must "test phrase"';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'must', 'type' => 'must'), array('word' => 'test phrase', 'type' => 'phrase')), $result);
    }

    public function testParseMustAndPhraseAndMustnt()
    {
        $q = '+must "test phrase" -mustnt';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'must', 'type' => 'must'), array('word' => 'test phrase', 'type' => 'phrase'), array('word' => 'mustnt', 'type' => 'mustnt')), $result);
    }

    public function testParseMissingPhraseEnd()
    {
        $q = '"test phrase';

        $result = $this->parser->parse($q);

        $this->assertEquals(array(array('word' => 'test phrase', 'type' => 'phrase')), $result);
    }

    public function testParseMissplacedPhraseStart()
    {
        $q = 'test phrase"bla';

        $result = $this->parser->parse($q);
print_r($result);
        $this->assertEquals(array(array('word' => 'test phrase', 'type' => 'phrase')), $result);
    }
}
