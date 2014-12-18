<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search;

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
     * @return array
     */
    public function parse($queryString)
    {
        $length = strlen($queryString);
        $buffer = array();
        $words = array();
        $type = 'should';
        $inBuffer = false;
        $inPhrase = false;
        for ($i = 0; $i < $length; $i++) {
            $token = $queryString[$i];
            echo $token.PHP_EOL;
            if ($token === ' ') {
                if (!$inBuffer) {
                    continue;
                }
                if (!$inPhrase) {
                    if (!$inBuffer) {
                        continue;
                    }
                    $inBuffer = false;
                    if (count($buffer)) {
                        $words[] = array('word' => implode('', $buffer), 'type' => $type);
                    }
                    $buffer = array();
                    $type = 'should';
                    continue;
                }
            } elseif ($token === '+') {
                if (!$inPhrase && !$inBuffer) {
                    $type = 'must';
                }
                continue;
            } elseif ($token === '-') {
                if (!$inPhrase && !$inBuffer) {
                    $type = 'mustnt';
                }
                continue;
            } elseif ($token === '"') {
                if ($inBuffer && !$inPhrase) {
                    $words[] = array('word' => implode('', $buffer), 'type' => $type);
                    $type = 'phrase';
                    $inBuffer = false;
                    $inPhrase = false;
                    $buffer = array();
                    continue;
                } elseif (!$inBuffer && $inPhrase) {
                    // phrase end
                    $words[] = array('word' => implode('', $buffer), 'type' => $type);
                    $type = 'should';
                    $inBuffer = false;
                    $inPhrase = false;
                    $buffer = array();
                    continue;
                } elseif ($inPhrase && $inBuffer) {
                    // phrase end
                    $words[] = array('word' => implode('', $buffer), 'type' => $type);
                    $type = 'should';
                    $inBuffer = false;
                    $inPhrase = false;
                    $buffer = array();
                    continue;
                } else {
                    // phrase start
                    $inPhrase = true;
                    $type = 'phrase';
                    continue;
                }
            }

            $inBuffer = true;
            $buffer[] = $token;
        }

        if ($inBuffer) {
            $words[] = array('word' => implode('', $buffer), 'type' => $type);
        }

        return $words;
    }
}
