<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query\Parser;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Parser\Token;
use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Parser\Tokenizer;
use PHPUnit\Framework\TestCase;

/**
 * Tokenizer test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\FrontendSearchBundle\Search\Query\Parser\Tokenizer
 */
class TokenizerTest extends TestCase
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
            new Token(Token::QUOTE, '"'),
            new Token(Token::TERM, 'test'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'phrase'),
            new Token(Token::QUOTE, '"'),
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
            new Token(Token::QUOTE, '"'),
            new Token(Token::TERM, 'test'),
            new Token(Token::SPACE, ' '),
            new Token(Token::TERM, 'phrase'),
            new Token(Token::QUOTE, '"'),
        );

        $this->assertEquals($expected, $tokens);
    }
}
