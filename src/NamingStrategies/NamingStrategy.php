<?php

namespace BabenkoIvan\FluentArray\NamingStrategies;

interface NamingStrategy
{
    /**
     * @param string $key
     * @return string
     */
    public function transform(string $key): string;
}
