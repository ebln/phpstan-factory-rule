<?php

declare(strict_types=1);

$includes = [];
if (PHP_VERSION_ID <= 80000) {
    $includes[] = __DIR__ . '/phpstan-baseline-7.4.neon';
}

$config                             = [];
$config['includes']                 = $includes;
$config['parameters']['phpVersion'] = PHP_VERSION_ID;

return $config;
