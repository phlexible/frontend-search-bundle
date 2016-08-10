<?php


namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\ReplacingQueryStringEscaper;

/**
 * Class ReplacingQueryStringEscaperTest
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 * @author Stephan Wentz <swentz@brainbits.net>
 */
class ReplacingQueryStringEscaperTest extends \PHPUnit_Framework_TestCase
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
        $escaper = new ReplacingQueryStringEscaper();

        $escapedQueryString = $escaper->escapeQueryString($queryString);

        $this->assertSame($expectedQueryString, $escapedQueryString);
    }
}
