<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

interface StopConditionInterface
{
    public static function getName();

    public function setArguments(array $arguments);

    public function meet(array $context): bool;
}
