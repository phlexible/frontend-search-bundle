<?php


namespace Phlexible\Bundle\FrontendSearchBundle\Search\Query;

/**
 * Class Util
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 */
class Util
{

    /**
     * Escape illegal characters
     * Lightweight version of \Elastica\Util::escapeTerm
     * @param string $queryString
     * @return string
     */
    public static function escapeQuery($queryString)
    {
        $chars = [
            '\\',
            '/',
            ':',
            '{',
            '}',
        ];

        foreach ($chars as $char) {
            $queryString = str_replace($char, '\\'.$char, $queryString);
        }

        return $queryString;
    }
}
