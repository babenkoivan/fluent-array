<?php

namespace BabenkoIvan\FluentArray\NamingStrategies;

class NullStrategy implements NamingStrategy
{
    /**
     * @param string $key
     * @return string
     */
    public function transform(string $key): string
    {
        return $key;
    }
}
