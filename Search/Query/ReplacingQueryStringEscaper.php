<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Replacing query string escaper.
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 * @author Stephan Wentz <swentz@brainbits.net>
 */
class ReplacingQueryStringEscaper implements QueryStringEscaperInterface
{
    /**
     * {@inheritdoc}
     * Lightweight version of \Elastica\Util::escapeTerm.
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
