<?php

namespace BabenkoIvan\FluentArray\Tests\NamingStrategies;

use BabenkoIvan\FluentArray\NamingStrategies\NullStrategy;
use PHPUnit\Framework\TestCase;

class NullStrategyTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string $transformed
     * @param string $subject
     */
    public function testTransformMethod(string $transformed, string $subject)
    {
        $this->assertSame($transformed, (new NullStrategy())->transform($subject));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['_fooBar', '_fooBar'],
            ['_fooBar_', '_fooBar_'],
            ['_foo_Bar', '_foo_Bar'],
            ['_Foo_Bar', '_Foo_Bar'],
            ['foo_bar', 'foo_bar'],
            ['foo_bar_', 'foo_bar_']
        ];
    }
}
