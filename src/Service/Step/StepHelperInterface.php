<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step;

interface StepHelperInterface
{
    public function cloneStepsAndResetColor(array $steps): array;
}
