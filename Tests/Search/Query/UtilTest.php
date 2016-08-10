<?php


namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Util;

/**
 * Class UtilTest
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 */
class UtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function stringsWithIllegalCharactersProvider()
    {
        $backslash = <<<EOF
\
EOF;

        return [
          [$backslash."with backslash", $backslash.$backslash."with backslash"],
          [": with colon", "\: with colon"],
          ["/ with +slash", "\/ with +slash"],
          ["{ term in -curly brackets}", "\{ term in -curly brackets\}"],
        ];
    }

    /**
     * @param string $queryString
     * @param string $expectedQueryString
     * @dataProvider stringsWithIllegalCharactersProvider
     */
    public function testEscapeIllegalCharacters($queryString, $expectedQueryString)
    {
        $escapedQueryString = Util::escapeQuery($queryString);

        $this->assertSame($expectedQueryString, $escapedQueryString);
    }
}
