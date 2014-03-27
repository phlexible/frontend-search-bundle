<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSuggestSearch
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Frontend Suggest Search Boost
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSuggestSearch
 * @author      Marco Fischer <mf@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendSuggestSearch_Boost extends MWF_Core_Indexer_Boost_Abstract
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