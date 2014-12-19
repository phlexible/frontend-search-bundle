<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Query string parser
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryStringParser
{
    /**
     * @param string $queryString
     *
     * @return Term[]
     */
    public function parse($queryString)
    {
        $tokenizer = new Tokenizer();
        $result = new ParseResult();

        $inPhrase = false;
        $terms = array();
        $phraseTerms = array();
        $currentType = 'should';

        $tokens = $tokenizer->tokenize($queryString);
        foreach ($tokens as $token) {
            $type = $token->getType();
            $value = $token->getValue();

            if ($type === Token::MUST) {
                $currentType = 'must';
            } elseif ($type === Token::MUST_NOT) {
                $currentType = 'mustNot';
            } elseif ($type === Token::PHRASE_START) {
                $inPhrase = true;
            } elseif ($type === Token::PHRASE_END) {
                $terms[] = new Term($currentType, $phraseTerms);
                $inPhrase = false;
                $phraseTerms = array();
                $currentType = '';
            } elseif ($type === Token::TERM) {
                if ($inPhrase) {
                    $phraseTerms[] = $value;
                } else {
                    $terms[] = new Term($currentType, $value);
                }
            }
        }

        if ($inPhrase) {
            $terms[] = new Term($currentType, $phraseTerms);
        }

        return $terms;

        $length = strlen($queryString);
        $buffer = array();
        $occurance = ParseResult::SHOULD;
        $inBuffer = false;
        $inPhrase = false;
        $hasSpace = false;
        for ($i = 0; $i < $length; $i++) {
            $token = $queryString[$i];

            if ($token === ' ') {
                if (!$inBuffer) {
                    continue;
                }
                if (!$inPhrase) {
                    if (!$inBuffer) {
                        continue;
                    }
                    if (count($buffer)) {
                        $result->add($occurance, implode('', $buffer), ParseResult::TERM);
                    }
                    $inBuffer = false;
                    $hasSpace = false;
                    $buffer = array();
                    $occurance = ParseResult::SHOULD;
                    continue;
                } else {
                    $hasSpace = true;
                }
            } elseif ($token === '+') {
                if (!$inPhrase && !$inBuffer) {
                    $occurance = ParseResult::MUST;
                    continue;
                }
            } elseif ($token === '-') {
                if (!$inPhrase && !$inBuffer) {
                    $occurance = ParseResult::MUST_NOT;
                    continue;
                }
            } elseif ($token === '"') {
                if ($inBuffer && !$inPhrase) {
                    // phrase start, buffer end
                    $result->add($occurance, implode('', $buffer), ParseResult::TERM);
                    $occurance = ParseResult::SHOULD;
                    $inBuffer = false;
                    $inPhrase = false;
                    $hasSpace = false;
                    $buffer = array();
                    continue;
                } elseif (!$inBuffer && $inPhrase) {
                    // phrase end
                    if ($hasSpace) {
                        $result->add($occurance, implode('', $buffer), ParseResult::PHRASE);
                    } else {
                        $result->add($occurance, implode('', $buffer), ParseResult::TERM);
                    }
                    $occurance = ParseResult::SHOULD;
                    $inBuffer = false;
                    $inPhrase = false;
                    $hasSpace = false;
                    $buffer = array();
                    continue;
                } elseif ($inPhrase && $inBuffer) {
                    // phrase end
                    if ($inPhrase && !$hasSpace) {
                        $occurance = ParseResult::SHOULD;
                    }
                    if ($hasSpace) {
                        $result->add($occurance, implode('', $buffer), ParseResult::PHRASE);
                    } else {
                        $result->add($occurance, implode('', $buffer), ParseResult::TERM);
                    }
                    $occurance = ParseResult::SHOULD;
                    $inBuffer = false;
                    $inPhrase = false;
                    $hasSpace = false;
                    $buffer = array();
                    continue;
                } else {
                    // phrase start
                    $inPhrase = true;
                    $hasSpace = false;
                    $occurance = ParseResult::SHOULD;
                    continue;
                }
            }

            $inBuffer = true;
            $buffer[] = $token;
        }

        if ($inBuffer) {
            $type = ParseResult::TERM;

            if ($inPhrase) {
                $occurance = ParseResult::SHOULD;
                if ($hasSpace) {
                    $type = ParseResult::PHRASE;
                }
            }

            $result->add($occurance, implode('', $buffer), $type);
        }

        return $result;
    }
}
