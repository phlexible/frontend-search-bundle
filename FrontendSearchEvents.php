<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle;

/**
 * Frontend search events
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
interface FrontendSearchEvents
{
    /**
     * Before Search Event
     * Fired before a search operation
     */
    const BEFORE_SEARCH = 'phlexible_frontend_search.before_search';

    /**
     * Search Event
     * Fired after a search operation
     */
    const SEARCH = 'phlexible_frontend_search.search';
}
