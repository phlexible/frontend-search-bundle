<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Token.
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
