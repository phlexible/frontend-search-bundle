<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Exception\InvalidArgumentException;

/**
 * Query string parser result
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ParseResult
{
    const MUST = 'must';
    const MUST_NOT = 'mustNot';
    const SHOULD = 'should';

    const TERM = 'term';
    const PHRASE = 'phrase';

    /**
     * @var array
     */
    private $parts = array();

    /**
     * @param string $occurrence
     * @param string $text
     * @param string $type
     *
     * @return $this
     */
    public function add($occurrence, $text, $type = 'term')
    {
        if (!in_array($occurrence, array(self::MUST, self::MUST_NOT, self::SHOULD))) {
            throw new InvalidArgumentException("Occurance needs to be must, notNot or should. Got $occurrence.");
        }

        if (!in_array($type, array(self::TERM, self::PHRASE))) {
            throw new InvalidArgumentException("Type needs to be term or phrase. Got $type.");
        }

        $this->parts[] = array('occurrence' => $occurrence, 'text' => $text, 'type' => $type);

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->parts;
    }
}
