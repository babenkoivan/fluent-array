<?php

namespace BabenkoIvan\FluentArray\Tests\NamingStrategies;

use BabenkoIvan\FluentArray\NamingStrategies\CamelCaseStrategy;
use PHPUnit\Framework\TestCase;

class CamelCaseStrategyTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string $transformed
     * @param string $subject
     */
    public function testTransformMethod(string $transformed, string $subject)
    {
        $this->assertSame($transformed, (new CamelCaseStrategy())->transform($subject));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['FooBar', '_fooBar'],
            ['FooBar', '_fooBar_'],
            ['FooBar', '_foo_Bar'],
            ['FooBar', '_Foo_Bar'],
            ['FooBar', 'foo_bar'],
            ['FooBar', 'foo_bar_']
        ];
    }
}
