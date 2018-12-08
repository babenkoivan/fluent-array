# FluentArray

[![Packagist](https://img.shields.io/packagist/v/babenkoivan/fluent-array.svg)](https://packagist.org/packages/babenkoivan/fluent-array)
[![Packagist](https://img.shields.io/packagist/dt/babenkoivan/fluent-array.svg)](https://packagist.org/packages/babenkoivan/fluent-array)
[![Build Status](https://travis-ci.com/babenkoivan/fluent-array.svg?branch=master)](https://travis-ci.com/babenkoivan/fluent-array)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/babenkoivan/fluent-array)
[![Donate](https://img.shields.io/badge/donate-PayPal-blue.svg)](https://www.paypal.me/ivanbabenko)

* [Introduction](#introduction)
* [Installation](#installation)
* [Configuration](#configuration) 
* [Macros](#macros)
* [Fixed methods](#fixed-methods) 
* [Dynamic methods](#dynamic-methods) 
* [Implemented interfaces](#implemented-interfaces) 
* [Code Formatting](#code-formatting)

## Introduction

The fluent array library provides you with a convenient chainable interface. 
If you like object-oriented syntax or you just want to have more readable array declaration,
the fluent array is at your service.  

#### Basic usage

```php
$order = (new FluentArray())
    ->user()
        ->id(1)
        ->name('John')
    ->end()
    ->coupon('SALE10')
    ->status('delivered')
    ->products()
        ->push()
            ->id(1)
            ->name('iPhone X')
            ->price(1200)
        ->end()
        ->push()
            ->id(2)
            ->name('Beats By Dre Studio3')
            ->price(360)
        ->end()
    ->end();
```

If we convert the fluent array to an associative array, by calling `$order->toArray()`,
we will get the following output:

```php
[
    'user' => [
        'id' => 1,
        'name' => 'John'
    ],
    'coupon' => 'SALE10',
    'status' => 'delivered',
    'products' => [
        [
            'id' => 1,
            'name' => 'iPhone X',
            'price' => 1200
        ],
        [
            'id' => 2,
            'name' => 'Beats By Dre Studio3'
            'price' => 360
        ]
    ]
]
```

#### Storage array

Every time you call [set](#set) or [get]($get), or any other method, that modifies or retrieves the state, 
you update the internal storage of fluent array.

```php
$fluentArray = new FluentArray();

// we set the key `one` and the corresponding value `1` in the storage 
$fluentArray->set('one', 1);
    
// we get the value, that corresponds the key `one` from the storage
$fluentArray->get('one');
```

## Installation

Use composer to install the library:

```bash
composer require babenkoivan/fluent-array
```

## Configuration

The configuration allows you to change a fluent array [behavior](#naming-strategies) and add [new functionality](#macros).

#### Local scope

To configure a specific fluent array instance use local scope.

```php
$config = (clone FluentArray::globalConfig())
    ->namingStrategy(new CamelCaseStrategy());

$fluentArray = (new FluentArray($config))
    ->one(1)
    ->two(2);

// alternatively you can set configuration, using the `config` method
$fluentArray = (new FluentArray())
    ->config($config)
    ->one(1)
    ->two(2);

$fluentArray->all();
// ['One' => 1, 'Two' => 2]
```
 
#### Global scope

To configure all fluent arrays use global scope.

```php
$globalConfig = FluentArray::globalConfig();

$globalConfig->namingStrategy(new CamelCaseStrategy());

$fluentArray = (new FluentArray())
    ->one(1)
    ->two(2);
    
$fluentArray->all();
// ['One' => 1, 'Two' => 2]    
```

#### Macros

You can use macros to extend a fluent array functionality. 
It can be done via configuration in [global](#global-scope) or [local scope](#local-scope).

```php
$globalConfig = FluentArray::globalConfig();

$globalConfig
    ->macros()
        ->format(function (string $key, int $decimals = 0) {
            $value = $this->get($key);
        
            if (is_numeric($value)) {
                return number_format($value, $decimals);
            } else {
                return $value;
            }
        })
    ->end();
    
$fluentArray = (new FluentArray())
    ->set('one', 10.567)
    ->set('two', 2.89);
    
$fluentArray->format('one', 2);
// 10.57

$fluentArray->format('two', 1);
// 2.9    
```

#### Naming strategies

Naming strategies describe key transformation in [dynamic methods](#dynamic-methods).

For example, we want all our keys to be underscored in [the storage array](#storage-array). 

```php
$config = (clone FluentArray::globalConfig())
    ->namingStrategy(new UnderscoreStrategy());

$fluentArray = (new FluentArray($config))
    ->firstValue(1)
    ->secondValue(2);

$fluentArray->all();
// ['first_value' => 1, 'second_value' => 2]
```

Now we want them to be camel-cased.

```php
$config = (clone FluentArray::globalConfig())
    ->namingStrategy(new CamelCaseStrategy());

$fluentArray = (new FluentArray($config))
    ->firstValue(1)
    ->secondValue(2);

$fluentArray->all();
// ['first_value' => 1, 'second_value' => 2]
```

The supported naming strategies are:

Strategy | Example
---------|--------
CamelCaseStrategy | `MyValue`
NullStrategy | `myValue`
UnderscoreStrategy | `my_value`

The default naming strategy is `UnderscoreStrategy`. 

## Fixed methods

* [all](#all)
* [clean](#clean)
* [config](#config)
* [count](#count)
* [each](#each)
* [filter](#filter)
* [first](#first)
* [fromArray](#fromarray)
* [fromJson](#fromjson)
* [get](#get)
* [globalConfig](#globalconfig)
* [has](#has)
* [keys](#keys)
* [krsort](#krsort)
* [ksort](#ksort)
* [last](#last)
* [map](#map)
* [pluck](#pluck)
* [pushWhen](#pushwhen)
* [push](#push)
* [rsort](#rsort)
* [setWhen](#setwhen)
* [set](#set)
* [sort](#sort)
* [toArray](#toarray)
* [toJson](#tojson)
* [unset](#unset)
* [usort](#usort)
* [values](#values)
* [when](#when)

#### all

The `all` method returns [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->all();    
// ['one' => 1, 'two' => 2]
```

#### clean

The `clean` method removes all keys and values from [the storage array](#storage-array). 

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);

$fluentArray->all();    
// ['one' => 1, 'two' => 2]

$fluentArray->clean()->all();    
// []    
```

#### config

The `config` method allows you to set or retrieve [local configuration](#local-scope).

```php
$config = (new FluentArray())
    ->set('naming_strategy', new NullStrategy());
    
$fluentArray = (new FluentArray())
    ->config($config);
    
$fluentArray->config()->get('naming_strategy');
// instance of NullStrategy       
```

#### count

The `count` method returns the amount of values in [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2)
    ->set('three', 3);

$fluentArray->count();
// 3
```

#### each

The `each` method iterates over the values in [the storage array](#storage-array).

```php
$odds = [];

$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2)
    ->set('three', 3)
    ->set('four', 4);

$fluentArray->each(function ($value, $key) use (&$odds) {
    if ($value % 2 !== 0) {
        $odds[] = $value;
    }
});

$odds;
// [1, 3]
```

To stop the iteration return `false` from the callback.

```php
$counter = 0;

$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2)
    ->set('three', 3);

$fluentArray->each(function ($value, $key) use (&$counter) {
    if ($value > 1) {
        return false;
    }
    
    $counter++;
});

$counter;
// 1
```

#### filter

The `filter` method filters [the storage array](#storage-array) using the given callback.
Return `false` from the callback to remove a value.

```php
$sourceFluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$filteredFluentArray = $sourceFluentArray->filter(function ($value, $key) {
    return $value > 1;
});

$filteredFluentArray->all();
// ['two' => 2]    
```  

If callback is not specified, all values, that can be converted to `false` will be removed.

```php
$fluentArray = (new FluentArray())
    ->set('zero', 0)
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->filter()->all();
// ['one' => 1, 'two' => 2]    
```

#### first

The `first` method retrieves the first value from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->first();
// 1        
```

#### fromArray

The `fromArray` method converts an array to a fluent array.

```php
$array = ['one' => 1, 'two' => 2];

$fluentArray = FluentArray::fromArray($array);

$fluentArray->all();
// ['one' => 1, 'two' => 2]
```

#### fromJson

The `fromJson` method converts JSON to a fluent array.

```php
$json = '{"one":1,"two":2}';

$fluentArray = FluentArray::fromJson($json);

$fluentArray->all();
// ['one' => 1, 'two' => 2]
```

#### get

The `get` method retrieves the value from [the storage array](#storage-array), 
that corresponds the given key.

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->get('two');
// 2    
```

#### globalConfig

The `globalConfig` method allows you to set or retrieve [global configuration](#global-scope).

```php
$globalConfig = (new FluentArray())
    ->set('naming_strategy', new NullStrategy());
   
FluentArray::globalConfig($globalConfig);

FluentArray::globalConfig()->get('naming_strategy');
// instance of NullStrategy       
``` 

#### has

The `has` method checks if the given key exists in [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->has('one');
// true

$fluentArray->has('three');
// false    
```

#### keys

The `keys` method retrieves all the keys from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->keys();
// ['one', 'two'] 
```

#### krsort

The `krsort` method sorts [the storage array](#storage-array) by keys in descending order.
You can specify sort flags as a first argument.

```php
$fluentArray = (new FluentArray())
    ->set('b', 1)
    ->set('a', 2)
    ->set('c', 3);
    
$fluentArray->krsort(SORT_STRING)->all();
// ['c' => 3, 'b' => 1, 'a' => 2] 
```

#### ksort

The `ksort` method sorts [the storage array](#storage-array) by keys in ascending order.
You can specify sort flags as a first parameter.

```php
$fluentArray = (new FluentArray())
    ->set('b', 1)
    ->set('a', 2)
    ->set('c', 3);
    
$fluentArray->ksort(SORT_STRING)->all();
// ['a' => 2, 'b' => 1, 'c' => 3] 
```

#### last

The `last` method retrieves the last value from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->last();
// 2        
```

#### map

The `map` method applies the given callback to all values in [the storage array](#storage-array) 
and returns a new fluent array. 

```php
$sourceFluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);

$resultFluentArray = $sourceFluentArray->map(function ($value, $key) {
    return $value * 10;
});

$resultFluentArray->all();
// ['one' => 10, 'two' => 20]
```

#### pluck

The `pluck` method extracts values with the given key, from child fluent arrays to a new fluent array.

```php
$fluentArray = (new FluentArray())
    ->set('one', (new FluentArray())->set('id', 1))
    ->set('two', (new FluentArray())->set('id', 2));
    
$fluentArray->pluck('id')->all();
// [1, 2]   
```

#### push

The `push` method appends the given value to [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->push(1)
    ->push(2);
    
$fluentArray->all();
// [1, 2]    
```

Another way of using the `push` method:

```php
$fluentArray = (new FluentArray())
    ->push()
        ->one(1)
        ->two(2)
    ->end()
    ->push()
        ->three(3)
    ->end();
    
$fluentArray->toArray();
// [['one' => 1, 'two' => 2], ['three' => 3]]    
```

#### pushWhen

The `pushWhen` method appends the given value to [the storage array](#storage-array), 
if the first argument is equivalent to `true`.

```php
$fluentArray = (new FluentArray())
    ->pushWhen(true, 1)
    ->pushWhen(false, 2)
    ->pushWhen(function () { return true; }, 3);
    
$fluentArray->all();
// [1, 3]    
```

Another way of using the `pushWhen` method:

```php
$fluentArray = (new FluentArray())
    ->pushWhen(true)
        ->one(1)
    ->end(false)
    ->pushWhen(false)
        ->two(2)
    ->end()
    ->pushWhen(function () { return true; })
        ->three(3)
    ->end();
    
$fluentArray->toArray();
// [['one' => 1], ['three' => 3]]    
```

#### rsort

The `rsort` method sorts [the storage array](#storage-array) in descending order.
You can specify sort flags as a first parameter.

```php
$fluentArray = (new FluentArray())
    ->set('three', 3)
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->rsort(SORT_NUMERIC)->all();
// ['three' => 3, 'two' => 2, 'one' => 1]    
```

#### set

The `set` method sets the given key and value in [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->all();
// ['one' => 1, 'two' => 2]    
```

#### setWhen

The `setWhen` method sets the given key and value in [the storage array](#storage-array),
if the first argument is equivalent to `true`.  

```php
$fluentArray = (new FluentArray())
    ->setWhen(true, 'one', 1)
    ->setWhen(false, 'two', 2)
    ->setWhen(function () { return true; }, 'three', 3);
    
$fluentArray->all();
// ['one' => 1, 'three' => 3]    
```

#### sort

The `sort` method sorts [the storage array](#storage-array) in ascending order.
You can specify sort flags as a first parameter.

```php
$fluentArray = (new FluentArray())
    ->set('three', 3)
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->sort(SORT_NUMERIC)->all();
// ['one' => 1, 'two' => 2, 'three' => 3]    
```

#### toArray

The `toArray` method converts a fluent array to an array.

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);

$fluentArray->toArray();
// ['one' => 1, 'two' => 2]
```

#### toJson

The `toJson` method converts a fluent array to JSON.

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);

$fluentArray->toJson();
// "{"one":1,"two":2}"
```

#### unset

The `unset` method removes [the storage array](#storage-array) value by the given key.

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->unset('one')->all();
// ['two' => 2]    
```

#### usort

The `usort` method sorts [the storage array](#storage-array) using the given comparison function.

```php
$fluentArray = (new FluentArray())
    ->set('three', 3)
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->usort(function ($a, $b) {
    return $a <=> $b;
});    
    
$fluentArray->all();
// ['one' => 1, 'two' => 2, 'three' => 3]    
```

#### values

The `values` method retrieves all the values from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->all();
// [1, 2]    
```

#### when

The `when` method executes the given callback, if the first argument is equivalent to `true`.  

```php
$fluentArray = new FluentArray();

$fluentArray->when(true, function () use ($fluentArray) {
    $fluentArray->set('one', 1);
});

$fluentArray->when(false, function () use ($fluentArray) {
    $fluentArray->set('two', 2);
});

$fluentArray->when(
    function () {
        return true;
    }, 
    function () use ($fluentArray) {
        $fluentArray->set('three', 3);
    }
);

$fluentArray->all();
// ['one' => 1, 'three' => 3]
```

You can specify a default callback, that will be executed,
if the first argument is equivalent to `false`.

```php
$fluentArray = new FluentArray();

$fluentArray->when(
    false, 
    function () use ($fluentArray) {
        $fluentArray->set('one', 1);
    },
    function () use ($fluentArray) {
        $fluentArray->set('two', 2);
    }
);

$fluentArray->all();
// ['two' => 2]
```

## Dynamic methods

* [Dynamic setter](#dynamic-setter)
* [Dynamic getter](#dynamic-getter)
* [Dynamic has](#dynamic-has)
* [Dynamic pluck](#dynamic-pluck)
* [Dynamic unset](#dynamic-unset)

#### Dynamic setter

You can also set a key-value pair in [the storage array](#storage-array) using a dynamic setter.

```php
$fluentArray = (new FluentArray())
    ->one(1)
    ->two(2);
    
$fluentArray->all();
// ['one' => 1, 'two' => 2]    
```

If you want to set the key, that is reserved for a method name, you can escape it.

```php
$fluentArray = (new FluentArray())
    ->{'\set'}(1)
    ->{'\get'}(2);
    
$fluentArray->all();
// ['set' => 1, 'get' => 2]    
```

Add `When` to set the given value if the first argument is equivalent to `true`.

```php
$fluentArray = (new FluentArray())
    ->oneWhen(true, 1)
    ->twoWhen(false, 2)
    ->threeWhen(function () { return true; }, 3);
    
$fluentArray->all();
// ['one' => 1, 'three' => 3]    
```

You can also chain creation of child fluent arrays.

```php
$fluentArray = (new FluentArray())
    ->one()
        ->two(3)
    ->end()
    ->three()
        ->four(4)
        ->five(5)
    ->end();
    
$fluentArray->toArray();
// ['one' => ['two' => 2], 'three' => ['four' => 4, 'five' => 5]]    
``` 

#### Dynamic getter

To retrieve a value from [the storage array](#storage-array) you can use a dynamic getter.

```php
$fluentArray = (new FluentArray())
    ->one(1)
    ->two(2);
    
$fluentArray->two();
// 2    
```

#### Dynamic has

To check if a key exists in [the storage array](#storage-array) you can use a dynamic `has` method.

```php
$fluentArray = (new FluentArray())
    ->one(1)
    ->two(2);
    
$fluentArray->hasOne();
// true

$fluentArray->hasThree();
// false    
```

#### Dynamic pluck

To extract values from child fluent arrays you can use a dynamic `pluck` method.

```php
$fluentArray = (new FluentArray())
    ->one()
        ->id(1)
    ->end()
    ->two()
        ->id(2)
    ->end();
    
$fluentArray->pluckId()->all();
// [1, 2]   
```

#### Dynamic unset

To remove a value from [the storage array](#storage-array) you can use a dynamic `unset` method.

```php
$fluentArray = (new FluentArray())
    ->one(1)
    ->two(2);
    
$fluentArray->unsetOne()->all();
// ['two' => 2]    
```

## Implemented interfaces

* [Countable](#countable)
* [Serializable](#serializable)
* [ArrayAccess](#arrayaccess)
* [IteratorAggregate](#iteratoraggregate)

#### Countable 

The `Countable` interface provides the `count` method support.
[See more here](http://php.net/manual/en/class.countable.php).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
count($fluentArray);
// 2
```

#### Serializable 

The `Serializable` interface provides serialization support.
[See more here](http://php.net/manual/en/class.serializable.php).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$serialized = serialize($fluentArray);
$unserialized = unserialize($serialized);

$unserialized->all();
// ['one' => 1, 'two' => 2]    
```

#### ArrayAccess 

The `ArrayAccess` interface provides array access.
[See more here](http://php.net/manual/en/class.arrayaccess.php).

```php
$fluentArray = new FluentArray();

$fluentArray['one'] = 1;
$fluentArray['two'] = 2;

$fluentArray['two'];
// 2
```

#### IteratorAggregate

The `IteratorAggregate` interface enables iteration over [the storage array](#storage-array).
[See more here](http://php.net/manual/en/class.iteratoraggregate.php).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
foreach ($fluentArray as $key => $value) {
    $fluentArray->set($key, $value * 10);
}    

$fluentArray->all();
// ['one' => 10, 'two' => 20]
```

## Code formatting

If you use `PhpStorm` and code auto formatting, you will likely face the issue, that the following code:

```php
$fluentArray = (new FluentArray())
    ->one()
        ->id(1)
    ->end()
    ->two()
        ->id(2)
    ->end();
```  

Will be transformed by `PhpStorm` to:

```php
$fluentArray = (new FluentArray())
    ->one()
    ->id(1)
    ->end()
    ->two()
    ->id(2)
    ->end();
``` 

Now the code is less readable, but luckily we can configure `PhpStorm` to disable auto formatting the specified peace of code.
To do so, open `PhpStorm` preferences, go to the `Editor > Code Style` section and select option `Enable formatter markers in comments`.

Now you can turn the formatter off for the specific part of your code:

```php
// @formatter:off
$fluentArray = (new FluentArray())
    ->one()
        ->id(1)
    ->end()
    ->two()
        ->id(2)
    ->end();
// @formatter:on
```  
