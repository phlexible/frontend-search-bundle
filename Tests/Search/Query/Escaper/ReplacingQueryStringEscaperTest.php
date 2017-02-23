<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Tests\Search\Query\Escaper;

use Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper\ReplacingQueryStringEscaper;
use PHPUnit\Framework\TestCase;

/**
 * Replacing query string escaper test.
 *
 * @author Tim Hoepfner <thoepfner@brainbits.net>
 * @author Stephan Wentz <swentz@brainbits.net>
 *
 * @covers \Phlexible\Bundle\FrontendSearchBundle\Search\Query\Escaper\ReplacingQueryStringEscaper
 */
class ReplacingQueryStringEscaperTest extends TestCase
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
            [$backslash.'with backslash', $backslash.$backslash.'with backslash'],
            [': with colon', "\: with colon"],
            ['/ with +slash', "\/ with +slash"],
            ['{ term in -curly brackets}', "\{ term in -curly brackets\}"],
            ['{}', "\{\}"],
            ['[ term in -square +brackets]', "\[ term in -square +brackets\]"],
            ['[]', "\[\]"],
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
