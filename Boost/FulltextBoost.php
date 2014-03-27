<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendFulltextSearch
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Frontend Fulltext Search Boost
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendFulltextSearch
 * @author      Marco Fischer <mf@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendFulltextSearch_Boost extends MWF_Core_Indexer_Boost_Abstract
{
    protected $_customBoosts = array(
        'copy'  => 1,
        'tags'  => 1.5,
        'title' => 1.25
    );

    protected $_customPrecision = array(
        'copy'  => 0.7,
        'tags'  => 0.9,
        'title' => 0.8
    );
}