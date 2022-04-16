<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

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
            $this->executeCommands($transition->getCommands(), $step->getColor(), $driver);
        }
        foreach ($step->getPlaces() as $place => $tokens) {
            $place = $revision->getPlace($place);
            if ($place instanceof PlaceInterface) {
                $this->executeCommands($place->getCommands(), $step->getColor(), $driver);
            }
        }
    }

    protected function executeCommands(array $commands, ColorInterface $color, RemoteWebDriver $driver): void
    {
        foreach ($commands as $command) {
            if ($command instanceof CommandInterface) {
                $this->commandRunnerManager->run($command, $color, $driver);
            }
        }
    }
}
