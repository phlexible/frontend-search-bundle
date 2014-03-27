<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent;

/**
 * Frontend search events
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
interface Events
{
    /**
     * Before Search Event
     * Fired before a search operation
     */
    const BEFORE_SEARCH = 'frontendfulltextsearch.before_search';

    /**
     * Search Event
     * Fired after a search operation
     */
    const SEARCH = 'frontendfulltextsearch.search';
}
