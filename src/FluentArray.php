<?php

namespace BabenkoIvan\FluentArray;

use BabenkoIvan\FluentArray\NamingStrategies\UnderscoreStrategy;

class FluentArray implements Configurable
{
    use HasConfiguration;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * @param FluentArray|null $config
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
     * @return mixed
     */
    public function when($condition, callable $callback)
    {
        if (is_callable($condition) ? $condition() : $condition) {
            return $callback();
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
    public function set(string $key, $value): self
    {
        $this->storage[$key] = $value;
        return $this;
    }

    /**
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
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->storage[$key];
    }

    /**
     * @param string $macro
     * @return bool
     */
    public function hasMacro(string $macro): bool
    {
        $config = $this->config();

        return
            $config->has('macros') &&
            $config->macros()->has($macro);
    }

    /**
     * @param $value
     * @return self
     */
    public function push($value): self
    {
        $this->storage[] = $value;
        return $this;
    }

    /**
     * @param callable|bool $condition
     * @param mixed $value
     * @return self
     */
    public function pushWhen($condition, $value): self
    {
        return $this->when($condition, function () use ($value) {
            return $this->push($value);
        });
    }

    /**
     * @param callable $callback
     * @return FluentArray
     */
    public function map(callable $callback)
    {
        $array = array_map($callback, $this->storage, array_keys($this->storage));
        return static::fromArray($array);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->storage as $key => $value) {
            $key = $this->transformKey($key);

            if ($callback($value, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * @param array $array
     * @return FluentArray
     */
    public static function fromArray(array $array)
    {
        $fluentArray = new static();

        foreach ($array as $key => $value) {
            $fluentArray->set($key, is_array($value) ? static::fromArray($value) : $value);
        }

        return $fluentArray;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof static ? $value->toArray() : $value;
        }, $this->storage);
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

        // process fluent has
        if (preg_match('/^has(.+?)$/', $method, $match)) {
            $key = $this->transformKey(lcfirst($match[1]));
            return $this->has($key);
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

        $keyTransformation = array_merge(
            $config->get('key_transformation') ?? [],
            $defaultConfig->get('key_transformation')
        );

        if (isset($keyTransformation[$key])) {
            return $keyTransformation[$key];
        }

        $namingStrategy = $config->get('naming_strategy') ?? $defaultConfig->get('naming_strategy');

        return $namingStrategy->transform($key);
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
     * @return FluentArray
     */
    protected static function defaultConfig()
    {
        return (new FluentArray())
            ->set('naming_strategy', new UnderscoreStrategy())
            ->set('key_transformation', [
                'keyTransformation' => 'key_transformation',
                'namingStrategy' => 'naming_strategy',
                'macros' => 'macros'
            ]);
    }
}
