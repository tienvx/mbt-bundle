<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;

interface StopConditionInterface extends PluginInterface
{
    public function setArguments(array $arguments);

    public function meet(array $context): bool;
}
