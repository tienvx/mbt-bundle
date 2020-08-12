<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
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

    public function run(StepInterface $step): void
    {
        if (!$this->canRun()) {
            throw new RuntimeException('Need to set up before running step');
        }
        $this->executeTransitionCommands($step->getTransition());
        foreach ($step->getMarking()->getPlaceMarkings() as $placeMarking) {
            $place = $placeMarking->getPlace();
            if ($place instanceof PlaceInterface) {
                $this->executePlaceCommands($place);
            }
        }
    }

    protected function executeTransitionCommands(TransitionInterface $transition): void
    {
        foreach ($transition->getActions() as $action) {
            $this->helper->replay($action);
        }
    }

    protected function executePlaceCommands(PlaceInterface $place): void
    {
        foreach ($place->getAssertions() as $assertion) {
            $this->helper->replay($assertion);
        }
    }
}
