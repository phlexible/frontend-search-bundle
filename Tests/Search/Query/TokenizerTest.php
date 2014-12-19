<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

require_once __DIR__ . '/../../../Search/Query/Tokenizer.php';

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Token;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Tokenizer;


/**
 * Tokenizer test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function testTokenizeSingleTerm()
    {
        $q = 'test';

        $tokens = $this->tokenizer->tokenize($q);
        $expected = array(
            new Token(Token::TERM, 'test'),
        );

        $this->assertEquals($expected, $tokens);
    }

    public function testTokenizeMultipleTerms()
    {
        $q = 'test word';

        $tokens = $this->tokenizer->tokenize($q);
        $expected = array(
            new Token(Token::TERM, 'test'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'word'),
        );

        $this->assertEquals($expected, $tokens);
    }

    public function testTokenizeMultipleTermsWithPhrases()
    {
        $q = 'hello "test phrase"';

        $tokens = $this->tokenizer->tokenize($q);
        $expected = array(
            new Token(Token::TERM, 'hello'),
            new Token(Token::SPACE, ' '),
            new Token(Token::PHRASE_START, '"'),
            new Token(Token::TERM, 'test'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'phrase'),
            new Token(Token::PHRASE_END, '"'),
        );

        $this->assertEquals($expected, $tokens);
    }

    public function testTokenizeComplex()
    {
        $q = '+hello -my nice "test phrase"';

        $tokens = $this->tokenizer->tokenize($q);
        $expected = array(
            new Token(Token::MUST, '+'),
            new Token(Token::TERM, 'hello'),
            new Token(Token::SPACE, ' '),
            new Token(Token::MUST_NOT, '-'),
            new Token(Token::TERM, 'my'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'nice'),
            new Token(Token::SPACE, ' '),
            new Token(Token::PHRASE_START, '"'),
            new Token(Token::TERM, 'test'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'phrase'),
            new Token(Token::PHRASE_END, '"'),
        );

        $this->assertEquals($expected, $tokens);
    }
}
