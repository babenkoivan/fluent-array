<?php

namespace BabenkoIvan\FluentArray;

use BabenkoIvan\FluentArray\NamingStrategies\UnderscoreStrategy;
use Countable;
use Serializable;

class FluentArray implements Configurable, Countable, Serializable
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
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, $value)
    {
        $this->storage[$key] = is_array($value) ? static::fromArray($value) : $value;
        return $this;
    }

    /**
     * @param callable|bool $condition
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setWhen($condition, string $key, $value)
    {
        return $this->when($condition, function () use ($key, $value) {
            return $this->set($key, $value);
        });
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->has($key) ? $this->storage[$key] : null;
    }

    /**
     * @param $value
     * @return self
     */
    public function push($value)
    {
        $this->storage[] = is_array($value) ? static::fromArray($value) : $value;
        return $this;
    }

    /**
     * @param callable|bool $condition
     * @param mixed $value
     * @return self
     */
    public function pushWhen($condition, $value)
    {
        return $this->when($condition, function () use ($value) {
            return $this->push($value);
        });
    }

    /**
     * @param string $key
     * @return self
     */
    public function unset(string $key)
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function clean()
    {
        $this->storage = [];
        return $this;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return reset($this->storage);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        return end($this->storage);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->storage;
    }

    /**
     * @param string $key
     * @return self
     */
    public function pluck(string $key)
    {
        $fluentArray = new static();

        $this->each(function ($item) use ($key, $fluentArray) {
            if ($item instanceof static && $item->has($key)) {
                $value = $item->get($key);
                $fluentArray->push($value);
            }
        });

        return $fluentArray;
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->storage);
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return array_values($this->storage);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->storage);
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback)
    {
        $values = array_map($callback, $this->values(), $this->keys());
        return static::fromArray(array_combine($this->keys(), $values));
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function each(callable $callback)
    {
        foreach ($this->storage as $key => $value) {
            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * @param array $array
     * @return self
     */
    public static function fromArray(array $array)
    {
        $fluentArray = new static();

        foreach ($array as $key => $value) {
            $fluentArray->set($key, $value);
        }

        return $fluentArray;
    }

    /**
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
     * @param string $json
     * @return self
     */
    public static function fromJson(string $json)
    {
        $array = json_decode($json, true);
        return static::fromArray($array);
    }

    /**
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
            $key = $this->transformKey(lcfirst($match[2]));
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
     * @param string $key
     * @return string
     */
    protected function transformKey(string $key)
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
     * @return mixed
     */
    protected function callSet(string $method, array $args)
    {
        $self = $this;

        preg_match('/^(\\\)?(.+?)(When)?$/', $method, $match);

        $key = $this->transformKey($match[2]);
        $condition = isset($match[3]) ? array_shift($args) : true;

        switch (count($args)) {
            case 0:
                $config = clone static::globalConfig();

                if (!$config->has('macros')) {
                    $config->set('macros', new static());
                }

                $config
                    ->get('macros')
                    ->set('end' . ucfirst($method), function () use ($self, $condition, $key) {
                        $self->setWhen($condition, $key, $this);
                        return $self;
                    });

                return new static($config);
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
    protected static function defaultConfig()
    {
        return (new static())
            ->set('naming_strategy', new UnderscoreStrategy());
    }
}
