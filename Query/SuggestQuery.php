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
 * Frontend Suggest Search Query
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSuggestSearch
 * @author      Marco Fischer <mf@brainbits.net>
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendSuggestSearch_Query extends MWF_Core_Indexer_Query_Abstract
{
    /**
     * Document types to find.
     *
     * @var array
     */
    protected $_documentTypes = array('media', 'elements');

    /**
     * @var array
     */
    protected $_fields = array('title', 'tags');

    /**
     * @var string
     */
    protected $_label = 'Frontend suggest search';
}