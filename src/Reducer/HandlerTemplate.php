<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Definition;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Runner as StepsRunner;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\ShortestPathStrategy;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\StrategyInterface as StepsBuilderStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class HandlerTemplate implements HandlerInterface
{
    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var BugHelper
     */
    protected $bugHelper;

    /**
     * @var ModelHelper
     */
    protected $modelHelper;

    /**
     * @var StepsRunner
     */
    protected $stepsRunner;

    public function __construct(
        SubjectManager $subjectManager,
        MessageBusInterface $messageBus,
        BugHelper $bugHelper,
        ModelHelper $modelHelper,
        StepsRunner $stepsRunner
    ) {
        $this->subjectManager = $subjectManager;
        $this->messageBus = $messageBus;
        $this->bugHelper = $bugHelper;
        $this->modelHelper = $modelHelper;
        $this->stepsRunner = $stepsRunner;
    }

    public function handle(Bug $bug, int $length, int $from, int $to): void
    {
        $model = $bug->getModel()->getName();
        $steps = $bug->getSteps();

        if ($steps->getLength() !== $length) {
            // The reproduce path has been reduced.
            return;
        }

        if (!$this->extraValidate($steps, $from, $to)) {
            return;
        }

        $newSteps = $this->buildNewSteps($this->modelHelper->getDefinition($model), $steps, $from, $to);
        if ($newSteps->getLength() >= $steps->getLength()) {
            // New path is longer than or equals old path.
            return;
        }

        $this->run($model, $newSteps, $bug);
    }

    protected function extraValidate(Steps $steps, int $from, int $to): bool
    {
        return true;
    }

    protected function buildNewSteps(Definition $definition, Steps $steps, int $from, int $to): Steps
    {
        $stepsBuilder = new StepsBuilder();
        $stepsBuilder->setStrategy($this->getStepsBuilderStrategy($definition));

        return $stepsBuilder->create($steps, $from, $to);
    }

    protected function getStepsBuilderStrategy(Definition $definition): StepsBuilderStrategy
    {
        return new ShortestPathStrategy($definition);
    }

    protected function run(string $modelName, Steps $newSteps, Bug $bug): void
    {
        try {
            $model = $this->modelHelper->get($modelName);
            $subject = $this->subjectManager->createAndSetUp($modelName);
            $this->stepsRunner->run($newSteps, $model, $subject);
        } catch (Throwable $newThrowable) {
            if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                $this->bugHelper->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getReducerName()));
            }
        }
    }
}
