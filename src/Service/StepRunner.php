<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;

class StepRunner implements StepRunnerInterface
{
    protected SeleniumInterface $selenium;
    protected ?Helper $helper = null;

    public function __construct(SeleniumInterface $selenium)
    {
        $this->selenium = $selenium;
    }

    public function setUp(): void
    {
        $this->helper = $this->selenium->createHelper();
    }

    public function tearDown(): void
    {
        if ($this->helper) {
            $this->helper->quit();
            $this->helper = null;
        }
    }

    public function canRun(): bool
    {
        return (bool) $this->helper;
    }

    public function run(StepInterface $step, ModelInterface $model): void
    {
        if (!$this->canRun()) {
            throw new RuntimeException('Need to set up before running step');
        }
        $transition = $model->getTransition($step->getTransition());
        if ($transition instanceof TransitionInterface) {
            $this->executeTransitionActions($transition);
        }
        foreach ($step->getPlaces() as $place => $tokens) {
            $place = $model->getPlace($place);
            if ($place instanceof PlaceInterface) {
                $this->executePlaceAssertions($place);
            }
        }
    }

    protected function executeTransitionActions(TransitionInterface $transition): void
    {
        foreach ($transition->getActions() as $action) {
            $this->helper->replay($action);
        }
    }

    protected function executePlaceAssertions(PlaceInterface $place): void
    {
        foreach ($place->getAssertions() as $assertion) {
            $this->helper->replay($assertion);
        }
    }
}
