<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Query string escaper interface.
 *
 * @author Stephan Wentz <swentz@brainbits.net>
 */
interface QueryStringEscaperInterface
{
    /**
     * Escape illegal characters.
     *
     * @param string $queryString
     *
     * @return string
     */
    public function escapeQueryString($queryString);
}
