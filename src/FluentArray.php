<?php

namespace BabenkoIvan\FluentArray;

use ArrayAccess;
use ArrayIterator;
use BabenkoIvan\FluentArray\NamingStrategies\UnderscoreStrategy;
use Countable;
use IteratorAggregate;
use Serializable;

class FluentArray implements Configurable, Countable, Serializable, ArrayAccess, IteratorAggregate
{
    use HasConfiguration;

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @param self|null $config
     */
    public function __construct(FluentArray $config = null)
    {
        if (isset($config)) {
            $this->config($config);
        }
    }

    /**
     * The method executes the given callback, if the first argument is equivalent to `true`.
     * @param callable|bool $condition
     * @param callable $callback
     * @param callable|null $default
     * @return mixed
     */
    public function when($condition, callable $callback, callable $default = null)
    {
        if (is_callable($condition) ? $condition($this) : $condition) {
            return $callback($this);
        } elseif ($default) {
            return $default($this);
        }

        return $this;
    }

    /**
     * The method checks if the given key exists in the storage array.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * The method sets the given key and value in the storage array.
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->storage[$key] = is_array($value) ? static::fromArray($value) : $value;
        return $this;
    }

    /**
     * The method sets the given key and value in the storage array, if the first argument is equivalent to `true`.
     * @param callable|bool $condition
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setWhen($condition, string $key, $value): self
    {
        return $this->when($condition, function () use ($key, $value) {
            return $this->set($key, $value);
        });
    }

    /**
     * The method retrieves the item value from the storage array, that corresponds the given key.
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->has($key) ? $this->storage[$key] : null;
    }

    /**
     * The method appends the given value to the storage array.
     * @param mixed|null $value
     * @return self
     */
    public function push($value = null): self
    {
        $self = $this;

        if (is_null($value)) {
            return $this->deriveWithMacros('end', function () use ($self, $value) {
                $self->push($this);
                return $self;
            });
        } elseif (is_array($value)) {
            $this->storage[] = static::fromArray($value);
        } else {
            $this->storage[] = $value;
        }

        return $this;
    }

    /**
     * The method appends the given value to the storage array, if the first argument is equivalent to `true`.
     * @param callable|bool $condition
     * @param mixed|null $value
     * @return self
     */
    public function pushWhen($condition, $value = null): self
    {
        $self = $this;

        if (is_null($value)) {
            return $this->deriveWithMacros('end', function () use ($self, $condition) {
                $self->pushWhen($condition, $this);
                return $self;
            });
        } else {
            return $this->when($condition, function () use ($value) {
                return $this->push($value);
            });
        }
    }

    /**
     * The method removes the storage array value by the given key.
     * @param string $key
     * @return self
     */
    public function unset(string $key): self
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);
        }

        return $this;
    }

    /**
     * The method removes all the items from the storage array.
     * @return self
     */
    public function clean(): self
    {
        $this->storage = [];
        return $this;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->push($value);
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->storage);
    }

    /**
     * The method retrieves the first item value from the storage array.
     * @return mixed
     */
    public function first()
    {
        return reset($this->storage);
    }

    /**
     * The method retrieves the last item value from the storage array.
     * @return mixed
     */
    public function last()
    {
        return end($this->storage);
    }

    /**
     * The method returns the storage array.
     * @return array
     */
    public function all(): array
    {
        return $this->storage;
    }

    /**
     * The method extracts item values from child fluent arrays to a new fluent array by the given key.
     * @param string $key
     * @return self
     */
    public function pluck(string $key): self
    {
        $fluentArray = $this->derive();

        $this->each(function ($item) use ($key, $fluentArray) {
            if ($item instanceof static && $item->has($key)) {
                $value = $item->get($key);
                $fluentArray->push($value);
            }
        });

        return $fluentArray;
    }

    /**
     * The method retrieves all the keys from the storage array.
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->storage);
    }

    /**
     * The method retrieves all the values from the storage array.
     * @return array
     */
    public function values(): array
    {
        return array_values($this->storage);
    }

    /**
     * The method returns amount of items in the storage array.
     * @return int
     */
    public function count(): int
    {
        return count($this->storage);
    }

    /**
     * The method sorts the storage array in ascending order.
     * @param int $sortFlags
     * @return self
     */
    public function sort(int $sortFlags = SORT_REGULAR): self
    {
        asort($this->storage, $sortFlags);
        return $this;
    }

    /**
     * The method sorts the storage array in descending order.
     * @param int $sortFlags
     * @return self
     */
    public function rsort(int $sortFlags = SORT_REGULAR): self
    {
        arsort($this->storage, $sortFlags);
        return $this;
    }

    /**
     * The method sorts the storage array using the given comparison function.
     * @param callable $callback
     * @return self
     */
    public function usort(callable $callback): self
    {
        uasort($this->storage, $callback);
        return $this;
    }

    /**
     * The method sorts the storage array by keys in ascending order.
     * @param int $sortFlags
     * @return self
     */
    public function ksort(int $sortFlags = SORT_REGULAR): self
    {
        ksort($this->storage, $sortFlags);
        return $this;
    }

    /**
     * The method sorts the storage array by keys in descending order.
     * @param int $sortFlags
     * @return self
     */
    public function krsort(int $sortFlags = SORT_REGULAR): self
    {
        krsort($this->storage, $sortFlags);
        return $this;
    }

    /**
     * The method applies the given callback to all items in the storage array and returns a new fluent array.
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        $keys = $this->keys();
        $values = array_map($callback, $this->values(), $this->keys());

        $fluentArray = $this->derive();

        foreach ($values as $index => $value) {
            $key = $keys[$index];
            $fluentArray->set($key, $value);
        }

        return $fluentArray;
    }

    /**
     * The method iterates over the items in the storage array.
     * To stop the iteration return `false` from the callback.
     * @param callable $callback
     * @return self
     */
    public function each(callable $callback): self
    {
        foreach ($this->storage as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * The method filters the storage array using the given callback.
     * Return `false` from the callback to remove an item.
     * @param callable|null $callback
     * @return self
     */
    public function filter(callable $callback = null): self
    {
        $fluentArray = $this->derive();

        $this->each(function ($value, $key) use ($callback, $fluentArray) {
            if (isset($callback) ? $callback($value, $key) : !empty($value)) {
                $fluentArray->set($key, $value);
            }
        });

        return $fluentArray;
    }

    /**
     * The method converts array to a fluent array.
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        $fluentArray = new static();

        foreach ($array as $key => $value) {
            $fluentArray->set($key, $value);
        }

        return $fluentArray;
    }

    /**
     * The method converts a fluent array to an array.
     * @return array
     */
    public function toArray(): array
    {
        $values = array_map(function ($value) {
            return $value instanceof static ? $value->toArray() : $value;
        }, $this->values(), $this->keys());

        return array_combine($this->keys(), $values);
    }

    /**
     * The method converts JSON to a fluent array.
     * @param string $json
     * @return self
     */
    public static function fromJson(string $json): self
    {
        $array = json_decode($json, true);
        return static::fromArray($array);
    }

    /**
     * The method converts a fluent array to JSON.
     * @return string
     */
    public function toJson(): string
    {
        $array = $this->toArray();
        return json_encode($array);
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->storage);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

    public function __clone()
    {
        $this->each(function ($value, $key) {
            $this->set($key, $value instanceof static ? clone $value : $value);
        });
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        // process macros
        if ($this->hasMacro($method)) {
            return $this->callMacro($method, $args);
        }

        // process fluent has, pluck and unset
        if (preg_match('/^(has|pluck|unset)(.+?)$/', $method, $match)) {
            $key = $this->transformKey($match[2]);
            return $this->{$match[1]}($key);
        }

        // process fluent getter
        if (count($args) == 0) {
            $key = $this->transformKey(ltrim($method, '\\'));

            if ($this->has($key)) {
                return $this->get($key);
            }
        }

        // process fluent setter
        return $this->callSet($method, $args);
    }

    /**
     * @return self
     */
    protected function derive(): self
    {
        $config = clone $this->config();
        return new static($config);
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return self
     */
    protected function deriveWithMacros(string $name, callable $callback): self
    {
        $config = clone $this->config();

        if (!$config->has('macros')) {
            $config->set('macros', new static());
        }

        $config
            ->get('macros')
            ->set($name, $callback);

        return new static($config);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function transformKey(string $key): string
    {
        $defaultConfig = static::defaultConfig();
        $config = $this->config();

        $namingStrategy = $config->get('naming_strategy') ?? $defaultConfig->get('naming_strategy');
        return $namingStrategy->transform($key);
    }

    /**
     * @param string $macro
     * @return bool
     */
    protected function hasMacro(string $macro): bool
    {
        $config = $this->config();

        return
            $config->has('macros') &&
            $config->macros()->has($macro);
    }

    /**
     * @param string $macro
     * @param array $args
     * @return mixed
     */
    protected function callMacro(string $macro, array $args)
    {
        $closure = $this
            ->config()
            ->get('macros')
            ->get($macro)
            ->bindTo($this);

        return $closure(...$args);
    }

    /**
     * @param string $method
     * @param array $args
     * @return self
     */
    protected function callSet(string $method, array $args): self
    {
        preg_match('/^(\\\)?(.+?)(When)?$/', $method, $match);

        $key = $this->transformKey($match[2]);
        $condition = isset($match[3]) ? array_shift($args) : true;

        switch (count($args)) {
            case 0:
                $self = $this;

                $fluentArray = $this->deriveWithMacros('end', function () use ($self, $condition, $key) {
                    return $self;
                });

                $this->setWhen($condition, $key, $fluentArray);
                return $fluentArray;
                break;
            case 1:
                return $this->setWhen($condition, $key, $args[0]);
                break;
            default:
                return $this->setWhen($condition, $key, $args);
                break;
        }
    }

    /**
     * @return self
     */
    protected static function defaultConfig(): self
    {
        return (new static())
            ->set('naming_strategy', new UnderscoreStrategy());
    }
}
