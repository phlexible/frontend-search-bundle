<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Query string escaper interface
 *
 * @author Stephan Wentz <swentz@brainbits.net>
 */
interface QueryStringEscaperInterface
{
    /**
     * Escape illegal characters
     *
     * @param string $queryString
     *
     * @return string
     */
    public function escapeQueryString($queryString);
}
