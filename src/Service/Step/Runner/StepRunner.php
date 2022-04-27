<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Values;

class StepRunner implements StepRunnerInterface
{
    protected CommandRunnerManagerInterface $commandRunnerManager;

    public function __construct(CommandRunnerManagerInterface $commandRunnerManager)
    {
        $this->commandRunnerManager = $commandRunnerManager;
    }

    public function run(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): void
    {
        $transition = $revision->getTransition($step->getTransition());
        if ($transition instanceof TransitionInterface) {
            $this->runCommands($transition->getCommands(), $driver);
        }
        foreach ($step->getPlaces() as $place => $tokens) {
            $place = $revision->getPlace($place);
            if ($place instanceof PlaceInterface) {
                $this->runCommands($place->getCommands(), $driver);
            }
        }
    }

    protected function runCommands(array $commands, RemoteWebDriver $driver): void
    {
        $values = new Values();
        foreach ($commands as $command) {
            if ($command instanceof CommandInterface) {
                $this->commandRunnerManager->run($command, $values, $driver);
            }
        }
    }
}
