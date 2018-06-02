<?php

namespace BabenkoIvan\FluentArray\NamingStrategies;

class CamelCaseStrategy implements NamingStrategy
{
    /**
     * @param string $key
     * @return string
     */
    public function transform(string $key): string
    {
        $key = preg_replace('/([\W_]+)/', ' ', $key);
        return str_replace(' ', '', ucwords($key));
    }
}
