<?php

namespace BabenkoIvan\FluentArray;

interface Configurable
{
    /**
     * @param FluentArray|null $defaultConfig
     * @return mixed
     */
    public static function defaultConfig(FluentArray $defaultConfig = null);

    /**
     * @param FluentArray|null $config
     * @return mixed
     */
    public function config(FluentArray $config = null);
}
