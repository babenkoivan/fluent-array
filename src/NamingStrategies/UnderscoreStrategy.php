<?php

namespace BabenkoIvan\FluentArray\NamingStrategies;

class UnderscoreStrategy implements NamingStrategy
{
    /**
     * @param string $key
     * @return string
     */
    public function transform(string $key): string
    {
        $key = preg_replace('/(_+)?[A-Z]+/', ' $0', $key);
        return strtolower(preg_replace('/[\s_]+/', '_', trim($key)));
    }
}
