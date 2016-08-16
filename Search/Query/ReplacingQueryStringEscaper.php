<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Replacing query string escaper
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 * @author Stephan Wentz <swentz@brainbits.net>
 */
class ReplacingQueryStringEscaper implements QueryStringEscaperInterface
{
    /**
     * {@inheritdoc}
     * Lightweight version of \Elastica\Util::escapeTerm
     */
    public function escapeQueryString($queryString)
    {
        $chars = [
            '\\',
            '/',
            ':',
            '{',
            '}',
            '[',
            ']',
        ];

        foreach ($chars as $char) {
            $queryString = str_replace($char, '\\'.$char, $queryString);
        }

        return $queryString;
    }
}
