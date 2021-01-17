<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class StepRunner implements StepRunnerInterface
{
    protected CommandRunnerManagerInterface $commandRunnerManager;

    public function __construct(CommandRunnerManagerInterface $commandRunnerManager)
    {
        $this->commandRunnerManager = $commandRunnerManager;
    }

    public function run(StepInterface $step, ModelInterface $model, RemoteWebDriver $driver): void
    {
        $transition = is_int($step->getTransition()) ? $model->getTransition($step->getTransition()) : null;
        if ($transition instanceof TransitionInterface) {
            $commands = $transition->getCommands();
        } else {
            // First step: execute starting commands (go to url, store variables...)
            $commands = $model->getStartCommands();
        }
        $this->executeCommands($commands, $step->getColor(), $driver);
        foreach ($step->getPlaces() as $place => $tokens) {
            $place = $model->getPlace($place);
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
