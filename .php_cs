<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'single_line_throw' => false,
        'class_definition' => ['single_line' => false],
        'single_space_after_construct' => false,
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
