<?php

namespace BabenkoIvan\FluentArray\Tests;

use BabenkoIvan\FluentArray\FluentArray;
use BabenkoIvan\FluentArray\HasConfiguration;
use PHPUnit\Framework\TestCase;

class HasConfigurationTest extends TestCase
{
    public function testGlobalConfigMethod()
    {
        $configurable = new class
        {
            use HasConfiguration;
        };

        $this->assertInstanceOf(FluentArray::class, $configurable::globalConfig());

        $newGlobalConfig = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $configurable::globalConfig($newGlobalConfig);

        $this->assertSame(1, $configurable::globalConfig()->get('foo'));
        $this->assertSame(2, $configurable::globalConfig()->get('bar'));
    }

    public function testConfigMethod()
    {
        $configurable = new class
        {
            use HasConfiguration;
        };

        $this->assertInstanceOf(FluentArray::class, $configurable->config());

        $newConfig = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $configurable->config($newConfig);

        $this->assertSame(1, $configurable->config()->get('foo'));
        $this->assertSame(2, $configurable->config()->get('bar'));
    }

    public function testDefaultConfig()
    {
        $configurable = new class
        {
            use HasConfiguration;

            protected static function defaultConfig()
            {
                return (new FluentArray())
                    ->set('foo', 1)
                    ->set('bar', 2);
            }
        };

        $this->assertInstanceOf(FluentArray::class, $configurable->config());
        $this->assertSame(1, $configurable->config()->get('foo'));
        $this->assertSame(2, $configurable->config()->get('bar'));
    }
}
