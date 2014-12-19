<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Suggest query
 *
 * @author Marco Fischer <mf@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class SuggestQuery
{
    /**
     * Document types to find.
     *
     * @var array
     */
    protected $documentTypes = array('media', 'elements');

    /**
     * @var array
     */
    protected $_fields = array('title', 'tags');

    /**
     * @var string
     */
    protected $label = 'Frontend suggest search';
}