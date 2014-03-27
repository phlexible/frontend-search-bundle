<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Boost;

use Phlexible\IndexerComponent\Boost\AbstractBoost;

/**
 * Suggest boost
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class SuggestBoost extends AbstractBoost
{
    protected $_customBoosts = array(
        'copy'  => 0.2,
        'tags'  => 0.5,
        'title' => 1
    );

    protected $_customPrecision = array(
        'copy'  => 0.5,
        'tags'  => 0.8,
        'title' => 0.8
    );
}