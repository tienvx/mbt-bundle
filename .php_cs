<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('app/var/cache/test/')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
