<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class StepRunner implements StepRunnerInterface
{
    protected CommandRunner $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    public function run(StepInterface $step, ModelInterface $model, RemoteWebDriver $driver): void
    {
        $transition = is_int($step->getTransition()) ? $model->getTransition($step->getTransition()) : null;
        if ($transition instanceof TransitionInterface) {
            $this->executeTransitionActions($transition, $driver);
        }
        foreach ($step->getPlaces() as $place => $tokens) {
            $place = $model->getPlace($place);
            if ($place instanceof PlaceInterface) {
                $this->executePlaceAssertions($place, $driver);
            }
        }
    }

    protected function executeTransitionActions(TransitionInterface $transition, RemoteWebDriver $driver): void
    {
        foreach ($transition->getActions() as $action) {
            $this->commandRunner->run($action, $driver);
        }
    }

    protected function executePlaceAssertions(PlaceInterface $place, RemoteWebDriver $driver): void
    {
        foreach ($place->getAssertions() as $assertion) {
            $this->commandRunner->run($assertion, $driver);
        }
    }
}
