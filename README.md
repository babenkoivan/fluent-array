# FluentArray

* [Introduction](#introduction)
* [Configuration](#configuration) 
* [Fixed methods](#fixed-methods) 
* [Dynamic methods](#dynamic-methods) 
* [Macros](#macros)
* [Implemented interfaces](#implemented-interfaces) 
* [Code Formatting](#code-formatting)

## Introduction

#### Basic usage

#### Storage array

## Configuration

### Naming strategies

### Local scope

### Global scope

## Fixed methods

* [all](#all)
* [clean](#clean)
* [config](#config)
* [count](#count)
* [each](#each)
* [filter](#filter)
* [first](#first)
* [fromArray](#from-array)
* [fromJson](#from-json)
* [get](#get)
* [globalConfig](#global-config)
* [has](#has)
* [keys](#keys)
* [krsort](#krsort)
* [ksort](#ksort)
* [last](#last)
* [map](#map)
* [pluck](#pluck)
* [pushWhen](#push-when)
* [push](#push)
* [rsort](#rsort)
* [setWhen](#set-when)
* [set](#set)
* [sort](#sort)
* [toArray](#to-array)
* [toJson](#to-json)
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

The `clean` method removes all the items from [the storage array](#storage-array). 

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

The `count` method returns amount of items in [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2)
    ->set('three', 3);

$fluentArray->count();
// 3
```

#### each

The `each` method iterates over the items in [the storage array](#storage-array).

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
Return `false` from the callback to remove an item.

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

If callback is not specified, all items, that can be converted to `false` will be removed.

```php
$fluentArray = (new FluentArray())
    ->set('zero', 0)
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->filter()->all();
// ['one' => 1, 'two' => 2]    
```

#### first

The `first` method retrieves the first item value from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->first();
// 1        
```

#### fromArray

The `fromArray` method converts array to a fluent array.

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

The `get` method retrieves the item value from [the storage array](#storage-array), 
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

The `has` method checks if a given key exists in [the storage array](#storage-array).

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
You can specify sort flags as a first parameter.

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

The `last` method retrieves the last item value from [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->set('one', 1)
    ->set('two', 2);
    
$fluentArray->last();
// 2        
```

#### map

The `map` method applies a given callback to all items in [the storage array](#storage-array) 
and returns a new fluent array instance. 

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

The `pluck` method extracts item values from child fluent arrays to a new fluent array instance by a given key.

```php
$fluentArray = (new FluentArray())
    ->set('one', (new FluentArray())->set('id', 1))
    ->set('two', (new FluentArray())->set('id', 2));
    
$fluentArray->pluck('id')->all();
// [1, 2]   
```

#### push

The `push` method appends a given value to [the storage array](#storage-array).

```php
$fluentArray = (new FluentArray())
    ->push(1)
    ->push(2);
    
$fluentArray->all();
// [1, 2]    
```

#### pushWhen

#### rsort

#### set

#### setWhen

#### sort

#### toArray

#### toJson

#### unset

#### usort

#### values

#### when

## Dynamic methods

## Implemented interfaces

## Code formatting
