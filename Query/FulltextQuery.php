<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchBundle\Query;

use Phlexible\IndexerBundle\Query\AbstractQuery;

/**
 * Fulltext query
 *
 * @author Marco Fischer <mf@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class FulltextQuery extends AbstractQuery
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
    protected $_fields = array('title', 'tags', 'copy');

    /**
     * @var string
     */
    protected $_label = 'Frontend fulltext search';
}