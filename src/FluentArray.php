<?php

namespace BabenkoIvan\FluentArray;

class FluentArray
{
    /**
     * @var array
     */
    private $storage = [];

    /**
     * @var FluentArray
     */
    private static $defaultConfig;

    /**
     * @var FluentArray
     */
    private $config;

    /**
     * @param FluentArray|null $config
     */
    public function __construct(FluentArray $config = null)
    {
        $this->config = $config;
    }

    /**
     * @return FluentArray
     */
    public static function defaultConfig()
    {
        if (!isset(static::$defaultConfig)) {
            static::$defaultConfig = new FluentArray();
        }

        return static::$defaultConfig;
    }

    /**
     * @return FluentArray
     */
    public function config()
    {
        if (!isset($this->config)) {
            $this->config = clone static::defaultConfig();
        }

        return $this->config;
    }

    /**
     * @param callable|bool $condition
     * @param callable $callback
     * @return mixed
     */
    public function when($condition, callable $callback)
    {
        if (is_callable($condition)) {
            $condition->bindTo($this);
            $invokeCallback = $condition();
        } else {
            $invokeCallback = $condition;
        }

        if ($invokeCallback) {
            $callback->bindTo($this);
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
     * @param string $macro
     * @param array $args
     * @return mixed
     */
    protected function callMacro(string $macro, array $args)
    {
        $closure = $this
            ->config()
            ->macros()
            ->$macro()
            ->bindTo($this);

        return $closure(...$args);
    }

    /**
     * @param string $key
     * @param array $args
     * @return mixed
     */
    protected function callSet(string $key, array $args)
    {
        $self = $this;

        preg_match('/^(.+?)(When)?$/', $key, $match);

        $realKey = $match[1];
        $condition = isset($match[2]) ? array_shift($args) : true;

        switch (count($args)) {
            case 0:
                $config = clone static::defaultConfig();

                if (!$config->has('macros')) {
                    $config->set('macros', new FluentArray());
                }

                $config
                    ->get('macros')
                    ->set('end' . ucfirst($key), function () use ($self, $condition, $realKey) {
                        $self->setWhen($condition, $realKey, $this);
                        return $self;
                    });

                return new FluentArray($config);
                break;
            case 1:
                return $this->setWhen($condition, $realKey, $args[0]);
                break;
            default:
                return $this->setWhen($condition, $realKey, $args);
                break;
        }
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if ($this->hasMacro($method)) {
            return $this->callMacro($method, $args);
        }

        if (preg_match('/^has(.+?)$/', $method, $match)) {
            return $this->has(lcfirst($match[1]));
        }

        $key = ltrim($method, '\\');

        if (count($args) == 0 && $this->has($key)) {
            return $this->get($key);
        }

        return $this->callSet($key, $args);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof FluentArray ? $value->toArray() : $value;
        }, $this->storage);
    }
}
