<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent;

use Phlexible\Component\Component;

/**
 * Frontend search component
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class FrontendSearchComponent extends Component
{
    public function __construct()
    {
        $this
            ->setVersion('0.7.0')
            ->setId('frontendsearch')
            ->setPackage('phlexible');
    }
}
