<?php

namespace App\Repositories;

use Illuminate\Contracts\Cache\Repository as Cache;

class CacheRepository
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $pattern
     *
     * @return array
     */
    public function keys($pattern)
    {
        $prefix = $this->cache->getStore()->getPrefix();
        $keys = $this->cache->getStore()->connection()->keys("{$prefix}$pattern");
        $keysWithoutPrefix = array_map(function ($key) use ($prefix) {
            return substr($key, strlen($prefix));
        }, $keys);

        return $keysWithoutPrefix;
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->cache, $method)) {
            return $this->cache->{$method}(...$parameters);
        }

        return $this->cache->getStore()->connection()->{$method}(...$parameters);
    }
}