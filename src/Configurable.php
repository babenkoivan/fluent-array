<?php

namespace BabenkoIvan\FluentArray;

interface Configurable
{
    /**
     * @param FluentArray|null $globalConfig
     * @return mixed
     */
    public static function globalConfig(FluentArray $globalConfig = null);

    /**
     * @param FluentArray|null $config
     * @return mixed
     */
    public function config(FluentArray $config = null);
}
