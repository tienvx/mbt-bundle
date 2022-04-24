<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step;

use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelperInterface;

class StepHelper implements StepHelperInterface
{
    protected ModelHelperInterface $modelHelper;

    public function __construct(ModelHelperInterface $modelHelper)
    {
        $this->modelHelper = $modelHelper;
    }

    public function cloneAndResetSteps(array $steps, RevisionInterface $revision): array
    {
        $lastColor = new Color();
        $lastPlaces = $this->modelHelper->getStartPlaceIds($revision);
        $newSteps = [];
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                throw new UnexpectedValueException(sprintf('Step must be instance of "%s".', StepInterface::class));
            }
            $newStep = clone $step;
            $newStep->setColor($lastColor);
            $newStep->setPlaces($lastPlaces);
            $newSteps[] = $newStep;
            $lastColor = clone $step->getColor();
            $lastPlaces = $step->getPlaces();
        }

        return $newSteps;
    }
}
