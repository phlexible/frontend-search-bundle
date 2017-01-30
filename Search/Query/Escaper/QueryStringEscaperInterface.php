<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper;

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
