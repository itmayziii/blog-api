<?php

require_once __DIR__ . '/../bootstrap/app.php';

/**
 * Make sure we are starting fresh on the cache
 */
$cache = $app->make(\Illuminate\Contracts\Cache\Repository::class);
$cache->clear();
