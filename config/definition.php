<?php

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode(TienvxMbtBundle::WEBDRIVER_URI)
                ->isRequired()
                ->cannotBeEmpty()
                ->defaultValue('http://localhost:4444')
            ->end()
        ->end()
        ->children()
            ->scalarNode(TienvxMbtBundle::UPLOAD_DIR)
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        ->end()
    ;
};
