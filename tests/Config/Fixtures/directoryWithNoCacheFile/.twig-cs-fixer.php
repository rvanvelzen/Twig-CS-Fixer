<?php

use TwigCsFixer\Cache\NullCacheManager;
use TwigCsFixer\Config\Config;

$config = new Config('Custom');
$config->setCacheFile(null);

return $config;