<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Tokenizer
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class Tokenizer
{
    const MUST = '+';
    const MUST_NOT = '-';
    const QUOTE = '"';
    const TERM = 'term';
    const PHRASE = 'phrase';

    /**
     * @param string $queryString
     *
     * @return Token[]
     */
    public function tokenize($queryString)
    {
        $length = strlen($queryString);
        $tokens = array();

        $buffer = array();
        for ($i = 0; $i < $length; $i++) {
            $char = $queryString[$i];

            if ($char === '+' && !count($buffer)) {
                $tokens[] = new Token(Token::MUST, $char);
            } elseif ($char === '-' && !count($buffer)) {
                $tokens[] = new Token(Token::MUST_NOT, $char);
            } elseif ($char === ' ') {
                if (count($buffer)) {
                    $tokens[] = new Token(Token::TERM, implode($buffer));
                    $buffer = array();
                }
                $tokens[] = new Token(Token::SPACE, $char);
            } elseif ($char === '"' && !count($buffer)) {
                $tokens[] = new Token(Token::PHRASE_START, $char);
            } elseif ($char === '"' && count($buffer)) {
                if (count($buffer)) {
                    $tokens[] = new Token(Token::TERM, implode($buffer));
                    $buffer = array();
                }
                $tokens[] = new Token(Token::PHRASE_END, $char);
            } else {
                $buffer[] = $char;
            }
        }

        if (count($buffer)) {
            $tokens[] = new Token(Token::TERM, implode($buffer));
        }

        return $tokens;
    }
}
