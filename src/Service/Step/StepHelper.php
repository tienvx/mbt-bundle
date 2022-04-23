<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step;

use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

class StepHelper implements StepHelperInterface
{
    public function cloneStepsAndResetColor(array $steps): array
    {
        $lastColor = null;
        $newSteps = [];
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                throw new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class));
            }
            $newStep = clone $step;
            $newStep->setColor($lastColor ?? new Color());
            $newSteps[] = $newStep;
            $lastColor = clone $step->getColor();
        }

        return $newSteps;
    }
}
