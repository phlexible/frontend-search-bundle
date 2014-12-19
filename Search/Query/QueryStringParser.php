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
            } elseif ($type === Token::QUOTE) {
                if ($inPhrase) {
                    $terms[] = new Term($phraseTerms, $currentType);
                    $inPhrase = false;
                    $phraseTerms = array();
                    $currentType = 'should';
                } else {
                    $inPhrase = true;
                }
            } elseif ($type === Token::TERM) {
                if ($inPhrase) {
                    $phraseTerms[] = $value;
                } else {
                    $terms[] = new Term($value, $currentType);
                    $currentType = 'should';
                }
            }
        }

        if ($inPhrase) {
            $terms[] = new Term($phraseTerms, $currentType);
        }

        return $terms;
    }
}
