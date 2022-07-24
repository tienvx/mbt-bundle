<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/config')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'single_line_throw' => false,
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
