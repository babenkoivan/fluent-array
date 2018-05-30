<?php

namespace BabenkoIvan\FluentArray;

trait HasConfiguration
{
    /**
     * @var FluentArray
     */
    private static $defaultConfig;

    /**
     * @var FluentArray
     */
    private $config;

    /**
     * @param FluentArray|null $defaultConfig
     * @return mixed
     */
    public static function defaultConfig(FluentArray $defaultConfig = null)
    {
        if (isset($defaultConfig)) {
            static::$defaultConfig = $defaultConfig;
        } else {
            if (!isset(static::$defaultConfig)) {
                static::$defaultConfig = new FluentArray();
            }

            return static::$defaultConfig;
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
                $this->config = clone static::defaultConfig();
            }

            return $this->config;
        }
    }
}
