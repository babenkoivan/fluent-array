<?php

namespace BabenkoIvan\FluentArray\Tests;

use BabenkoIvan\FluentArray\FluentArray;
use BabenkoIvan\FluentArray\NamingStrategies\UnderscoreStrategy;
use PHPUnit\Framework\TestCase;

class FluentArrayTest extends TestCase
{
    public function testConstruction()
    {
        $config = (new FluentArray())->set('foo', 'bar');
        $fluentArray = new FluentArray($config);

        $this->assertSame(
            $fluentArray->config(),
            $config
        );
    }

    public function testWhenMethod()
    {
        $callback = function (FluentArray $fluentArray) {
            return $fluentArray->get('callback');
        };

        $default = function (FluentArray $fluentArray) {
            return $fluentArray->get('default');
        };

        $trueCondition = function () {
            return true;
        };

        $falseCondition = function () {
            return false;
        };

        $fluentArray = (new FluentArray())
            ->set('callback', 'callback is invoked')
            ->set('default', 'default is invoked');

        $this->assertSame($fluentArray, $fluentArray->when(false, $callback));
        $this->assertSame('default is invoked', $fluentArray->when(false, $callback, $default));
        $this->assertSame('default is invoked', $fluentArray->when($falseCondition, $callback, $default));
        $this->assertSame('callback is invoked', $fluentArray->when(true, $callback));
        $this->assertSame('callback is invoked', $fluentArray->when(true, $callback, $default));
        $this->assertSame('callback is invoked', $fluentArray->when($trueCondition, $callback, $default));
    }

    public function testHasMethod()
    {
        $fluentArray = (new FluentArray())->set('foo', 'bar');
        $this->assertTrue($fluentArray->has('foo'));
    }

    public function testFluentHasMethod()
    {
        $fluentArray = (new FluentArray())->foo('bar');
        $this->assertTrue($fluentArray->hasFoo());
    }

    public function testSetAndGetMethods()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', ['first' => 2, 'second' => [3, 4]]);

        $this->assertSame(1, $fluentArray->get('foo'));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('bar'));
        $this->assertSame(2, $fluentArray->get('bar')->get('first'));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('bar')->get('second'));
        $this->assertSame(3, $fluentArray->get('bar')->get('second')->get(0));
        $this->assertSame(4, $fluentArray->get('bar')->get('second')->get(1));
        $this->assertNull($fluentArray->get('null'));
    }

    public function testFluentSetAndGetMethods()
    {
        $fluentArray = (new FluentArray())->foo('bar');
        $this->assertSame($fluentArray->foo(), 'bar');

        // @formatter:off
        $fluentArray = (new FluentArray())
            ->foo()
                ->one(1)
                ->two(2)
                ->bar()
                    ->three(3)
                ->end()
            ->end();
        // @formatter:on

        $this->assertSame(1, $fluentArray->foo()->one());
        $this->assertSame(2, $fluentArray->foo()->two());
        $this->assertSame(3, $fluentArray->foo()->bar()->three());
    }

    public function testSetWhenMethod()
    {
        $fluentArray = (new FluentArray())
            ->setWhen(true, 'foo', 1)
            ->setWhen(false, 'bar', 2);

        $this->assertSame(1, $fluentArray->get('foo'));
        $this->assertNull($fluentArray->get('bar'));
    }

    public function testPushMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('key', 'value')
            ->push('foo')
            ->push('bar');

        $this->assertSame('value', $fluentArray->get('key'));
        $this->assertSame('foo', $fluentArray->get(0));
        $this->assertSame('bar', $fluentArray->get(1));

        $fluentArray = (new FluentArray())
            ->push(1)
            ->push(['foo' => 'bar']);

        $this->assertSame(1, $fluentArray->get(0));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get(1));
        $this->assertSame('bar', $fluentArray->get(1)->get('foo'));
    }

    public function testPushWhenMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('key', 'value')
            ->pushWhen(true, 'foo')
            ->pushWhen(false, 'bar');

        $this->assertSame(
            ['key' => 'value', 'foo'],
            $fluentArray->toArray()
        );
    }

    public function testUnsetMethod()
    {
        $fluentArray = (new FluentArray())->set('foo', 'bar');
        $this->assertTrue($fluentArray->has('foo'));

        $fluentArray->unset('foo');
        $this->assertFalse($fluentArray->has('foo'));
    }

    public function testFluentUnsetMethod()
    {
        $fluentArray = (new FluentArray())->foo('bar');
        $this->assertTrue($fluentArray->hasFoo());

        $fluentArray->unsetFoo();
        $this->assertFalse($fluentArray->hasFoo());
    }

    public function testCleanMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $this->assertTrue($fluentArray->has('foo'));
        $this->assertTrue($fluentArray->has('bar'));

        $fluentArray->clean();

        $this->assertFalse($fluentArray->has('foo'));
        $this->assertFalse($fluentArray->has('bar'));
    }

    public function testOffsetSetMethod()
    {
        $fluentArray = new FluentArray();

        $fluentArray[] = 1;
        $fluentArray['foo'] = ['bar' => 2];

        $this->assertSame(1, $fluentArray->get(0));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('foo'));
        $this->assertSame(2, $fluentArray->get('foo')->get('bar'));
    }

    public function testOffsetExistsMethod()
    {
        $fluentArray = (new FluentArray())->set('foo', 1);

        $this->assertTrue(isset($fluentArray['foo']));
        $this->assertFalse(isset($fluentArray['bar']));
    }

    public function testOffsetGetMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->push(2);

        $this->assertSame(1, $fluentArray['foo']);
        $this->assertSame(2, $fluentArray[0]);
        $this->assertNull($fluentArray['bar']);
    }

    public function testOffsetUnsetMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        unset($fluentArray['foo']);

        $this->assertNull($fluentArray->get('foo'));
        $this->assertSame(2, $fluentArray->get('bar'));
    }

    public function testIteration()
    {
        $iterationResult = [];

        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        foreach ($fluentArray as $key => $value) {
            $iterationResult[$key] = $value;
        }

        $this->assertSame(
            ['foo' => 1, 'bar' => 2],
            $iterationResult
        );
    }

    public function testFirstMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $this->assertSame(1, $fluentArray->first());
    }

    public function testLastMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $this->assertSame(2, $fluentArray->last());
    }

    public function testAllMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $this->assertSame(
            ['foo' => 1, 'bar' => 2],
            $fluentArray->all()
        );
    }

    public function testPluckMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('id', 1)
            ->push((new FluentArray())->set('id', 2))
            ->push((new FluentArray())->set('foo', 3))
            ->push((new FluentArray())->set('id', 4))
            ->push((new FluentArray())->set('id', 5));

        $this->assertSame(
            [2, 4, 5],
            $fluentArray->pluck('id')->all()
        );
    }

    public function testFluentPluckMethod()
    {
        $fluentArray = (new FluentArray())
            ->id(1)
            ->push((new FluentArray())->id(2))
            ->push((new FluentArray())->foo(3))
            ->push((new FluentArray())->id(4))
            ->push((new FluentArray())->id(5));

        $this->assertSame(
            [2, 4, 5],
            $fluentArray->pluckId()->all()
        );
    }

    public function testKeysMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2)
            ->push(3);

        $this->assertSame(
            ['foo', 'bar', 0],
            $fluentArray->keys()
        );
    }

    public function testValuesMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $this->assertSame(
            [1, 2],
            $fluentArray->values()
        );
    }

    public function testCountMethod()
    {
        $fluentArray = (new FluentArray())
            ->push(1)
            ->set('foo', 'bar')
            ->push(3);

        $this->assertCount(3, $fluentArray);
    }

    public function testSortMethod()
    {
        $fluentArray = (new FluentArray())
            ->push('10')
            ->set('foo', 1)
            ->set('bar', 5);

        $this->assertSame(
            ['foo' => 1, 'bar' => 5, '10'],
            $fluentArray->sort()->toArray()
        );

        $this->assertSame(
            ['foo' => 1, '10', 'bar' => 5],
            $fluentArray->sort(SORT_STRING)->toArray()
        );
    }

    public function testRsortMethod()
    {
        $fluentArray = (new FluentArray())
            ->push('10')
            ->set('foo', 1)
            ->set('bar', 5);

        $this->assertSame(
            ['10', 'bar' => 5, 'foo' => 1],
            $fluentArray->rsort()->toArray()
        );

        $this->assertSame(
            ['bar' => 5, '10', 'foo' => 1],
            $fluentArray->rsort(SORT_STRING)->toArray()
        );
    }

    public function testUsortMethod()
    {
        $sourceFluentArray = (new FluentArray())
            ->set('foo', 7)
            ->push(1)
            ->push(5);

        $resultFluentArray = $sourceFluentArray->usort(function ($a, $b) {
            return $a <=> $b;
        });

        $this->assertSame(
            [1, 5, 'foo' => 7],
            $resultFluentArray->toArray()
        );
    }

    public function testKsortMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('2', 10)
            ->set('10', 3)
            ->set('1', 8)
            ->set('a', 1)
            ->set('b', 5);

        $this->assertSame(
            ['a' => 1, 'b' => 5, '1' => 8, '2' => 10, '10' => 3],
            $fluentArray->ksort()->toArray()
        );

        $this->assertSame(
            ['1' => 8, '10' => 3, '2' => 10, 'a' => 1, 'b' => 5],
            $fluentArray->ksort(SORT_STRING)->toArray()
        );
    }

    public function testKrsortMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('2', 10)
            ->set('10', 3)
            ->set('1', 8)
            ->set('a', 1)
            ->set('b', 5);

        $this->assertSame(
            ['10' => 3, '2' => 10, '1' => 8, 'b' => 5, 'a' => 1],
            $fluentArray->krsort()->toArray()
        );

        $this->assertSame(
            ['b' => 5, 'a' => 1, '2' => 10, '10' => 3, '1' => 8],
            $fluentArray->krsort(SORT_STRING)->toArray()
        );
    }

    public function testMapMethod()
    {
        $sourceFluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2);

        $resultFluentArray = $sourceFluentArray->map(function ($value, $key) {
            return $key == 'bar' ? $value * 10 : $value;
        });

        $this->assertSame(1, $resultFluentArray->get('foo'));
        $this->assertSame(20, $resultFluentArray->get('bar'));
    }

    public function testEachMethod()
    {
        $values = [];

        (new FluentArray())
            ->set('foo', 1)
            ->set('bar', 2)
            ->each(function ($value, $key) use (&$values) {
                if ($key == 'bar') {
                    return false;
                }

                $values[] = $value;
            });

        $this->assertSame([1], $values);
    }

    public function testFilterMethod()
    {
        $sourceFluentArray = (new FluentArray())
            ->set('foo', 0)
            ->set('bar', 3);

        $resultFluentArray = $sourceFluentArray->filter();

        $this->assertNull($resultFluentArray->get('foo'));
        $this->assertSame(3, $resultFluentArray->get('bar'));

        $resultFluentArray = $sourceFluentArray->filter(function ($value, $key) {
            return $key == 'foo';
        });

        $this->assertSame(0, $resultFluentArray->get('foo'));
        $this->assertNull($resultFluentArray->get('bar'));
    }

    public function testFromArrayMethod()
    {
        $array = [
            'foo' => 1,
            2,
            'child' => [
                'bar' => 3
            ]
        ];

        $fluentArray = FluentArray::fromArray($array);

        $this->assertSame(1, $fluentArray->get('foo'));
        $this->assertSame(2, $fluentArray->get(0));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('child'));
        $this->assertSame(3, $fluentArray->get('child')->get('bar'));
    }

    public function testToArrayMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->push(2)
            ->set('child', (new FluentArray())->set('bar', 3));

        $this->assertSame(
            ['foo' => 1, 2, 'child' => ['bar' => 3]],
            $fluentArray->toArray()
        );
    }

    public function testFromJsonMethod()
    {
        $json = '{"foo":1,"bar":[2,3]}';

        $fluentArray = FluentArray::fromJson($json);

        $this->assertSame(1, $fluentArray->get('foo'));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('bar'));
        $this->assertSame(2, $fluentArray->get('bar')->get(0));
        $this->assertSame(3, $fluentArray->get('bar')->get(1));
    }

    public function testToJsonMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', [2, 3]);

        $this->assertSame(
            '{"foo":1,"bar":[2,3]}',
            $fluentArray->toJson()
        );
    }

    public function testSerializeMethod()
    {
        $fluentArray = (new FluentArray())
            ->set('foo', 1)
            ->set('bar', [3, 4]);

        $this->assertSame(
            'C:35:"BabenkoIvan\FluentArray\FluentArray":100:{a:2:{s:3:"foo";i:1;s:3:"bar";' .
            'C:35:"BabenkoIvan\FluentArray\FluentArray":22:{a:2:{i:0;i:3;i:1;i:4;}}}}',
            serialize($fluentArray)
        );
    }

    public function testUnserializeMethod()
    {
        $serialized = 'C:35:"BabenkoIvan\FluentArray\FluentArray":100:{a:2:{s:3:"foo";i:1;s:3:"bar";' .
            'C:35:"BabenkoIvan\FluentArray\FluentArray":22:{a:2:{i:0;i:3;i:1;i:4;}}}}';

        $fluentArray = unserialize($serialized);

        $this->assertSame(1, $fluentArray->get('foo'));
        $this->assertInstanceOf(FluentArray::class, $fluentArray->get('bar'));
        $this->assertSame(3, $fluentArray->get('bar')->get(0));
        $this->assertSame(4, $fluentArray->get('bar')->get(1));
    }

    public function testCloning()
    {
        $sourceFluentArray = (new FluentArray())
            ->set('int', 123)
            ->set('fluent_arr', (new FluentArray())->set('arr', ['foo' => 'bar']));

        $clonedFluentArray = clone $sourceFluentArray;

        $sourceFluentArray
            ->set('int', 456)
            ->get('fluent_arr')
            ->get('arr')
            ->unset('foo');

        $this->assertSame(123, $clonedFluentArray->get('int'));
        $this->assertInstanceOf(FluentArray::class, $clonedFluentArray->get('fluent_arr'));
        $this->assertInstanceOf(FluentArray::class, $clonedFluentArray->get('fluent_arr')->get('arr'));
        $this->assertSame('bar', $clonedFluentArray->get('fluent_arr')->get('arr')->get('foo'));
    }

    public function testDefaultConfig()
    {
        $fluentArray = new FluentArray();
        $config = $fluentArray->config();

        $this->assertInstanceOf(FluentArray::class, $config);
        $this->assertInstanceOf(UnderscoreStrategy::class, $config->get('naming_strategy'));
    }
}
