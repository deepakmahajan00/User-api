<?php

use Sami\Sami;
use Sami\Parser\Filter\TrueFilter;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Resources')
    ->exclude('Tests')
    ->in(__DIR__.'/../src')
;

$sami = new Sami($iterator, array(
    'default_opened_level' => 2,
    'title'                => 'Canopy User API',
    'build_dir'            => __DIR__.'/../web-sami/build',
    'cache_dir'            => __DIR__.'/../web-sami/cache',
));

// include private properties and private methods
$sami['filter'] = function () {
    return new TrueFilter();
};

return $sami;
