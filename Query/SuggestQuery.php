<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Query;

use Phlexible\IndexerComponent\Query\AbstractQuery;

/**
 * Suggest query
 *
 * @author Marco Fischer <mf@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class SuggestQuery extends AbstractQuery
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