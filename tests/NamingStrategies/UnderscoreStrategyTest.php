<?php

namespace BabenkoIvan\FluentArray\Tests\NamingStrategies;

use BabenkoIvan\FluentArray\NamingStrategies\UnderscoreStrategy;
use PHPUnit\Framework\TestCase;

class UnderscoreStrategyTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string $transformed
     * @param string $subject
     */
    public function testTransformMethod(string $transformed, string $subject)
    {
        $this->assertSame($transformed, (new UnderscoreStrategy())->transform($subject));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['_foo_bar', '_fooBar'],
            ['_foo_bar_', '_fooBar_'],
            ['_foo_bar', '_foo_Bar'],
            ['_foo_bar', '_Foo_Bar'],
            ['foo_bar', 'foo_bar'],
            ['foo_bar_', 'foo_bar_']
        ];
    }
}
