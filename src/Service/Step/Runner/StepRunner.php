<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\Values;

class StepRunner implements StepRunnerInterface
{
    public function __construct(protected CommandManagerInterface $commandManager)
    {
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
                $this->commandManager->run(
                    $command->getCommand(),
                    $command->getTarget(),
                    $command->getValue(),
                    $values,
                    $driver
                );
            }
        }
    }
}
