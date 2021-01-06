<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

interface ReducerManagerInterface extends PluginManagerInterface
{
    public function getReducer(string $name): ReducerInterface;
}
