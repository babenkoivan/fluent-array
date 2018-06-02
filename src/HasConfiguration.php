<?php

namespace BabenkoIvan\FluentArray;

trait HasConfiguration
{
    /**
     * @var FluentArray
     */
    private static $globalConfig;

    /**
     * @var FluentArray
     */
    private $config;

    /**
     * @param FluentArray|null $globalConfig
     * @return mixed
     */
    public static function globalConfig(FluentArray $globalConfig = null)
    {
        if (isset($globalConfig)) {
            static::$globalConfig = $globalConfig;
        } else {
            if (!isset(static::$globalConfig)) {
                static::$globalConfig = static::defaultConfig();
            }

            return static::$globalConfig;
        }
    }

    /**
     * @param FluentArray|null $config
     * @return mixed
     */
    public function config(FluentArray $config = null)
    {
        if (isset($config)) {
            $this->config = $config;
            return $this;
        } else {
            if (!isset($this->config)) {
                $this->config = clone static::globalConfig();
            }

            return $this->config;
        }
    }

    /**
     * @return FluentArray
     */
    protected static function defaultConfig(): FluentArray
    {
        return new FluentArray();
    }
}
