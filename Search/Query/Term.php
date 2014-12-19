<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Token
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class Term
{
    const TERM = 'term';
    const PHRASE = 'phrase';

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $occurrence;

    /**
     * @param string $value
     * @param string $occurrence
     */
    public function __construct($value, $occurrence)
    {
        if (is_array($value) && count($value) === 1) {
            $value = current($value);
        }

        $this->value = $value;
        $this->occurrence = $occurrence;
    }

    /**
     * @return string
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}