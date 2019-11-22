<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\GraphHelper;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\ShortestPathStrategy;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\StrategyInterface as StepsBuilderStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Steps\StepsRunner;
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
     * @var GraphHelper
     */
    protected $graphHelper;

    /**
     * @var BugHelper
     */
    protected $bugHelper;

    public function __construct(
        SubjectManager $subjectManager,
        MessageBusInterface $messageBus,
        GraphHelper $graphHelper,
        BugHelper $bugHelper
    ) {
        $this->subjectManager = $subjectManager;
        $this->messageBus = $messageBus;
        $this->graphHelper = $graphHelper;
        $this->bugHelper = $bugHelper;
    }

    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to): void
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

        $newSteps = $this->buildNewSteps($workflow, $steps, $from, $to);
        if ($newSteps->getLength() >= $steps->getLength()) {
            // New path is longer than or equals old path.
            return;
        }

        $this->run($model, $newSteps, $bug, $workflow);
    }

    protected function extraValidate(Steps $steps, int $from, int $to): bool
    {
        return true;
    }

    protected function buildNewSteps(Workflow $workflow, Steps $steps, int $from, int $to): Steps
    {
        $stepsBuilder = new StepsBuilder();
        $stepsBuilder->setStrategy($this->getStepsBuilderStrategy($workflow));

        return $stepsBuilder->create($steps, $from, $to);
    }

    protected function getStepsBuilderStrategy(Workflow $workflow): StepsBuilderStrategy
    {
        $graph = $this->graphHelper->build($workflow);

        return new ShortestPathStrategy($graph);
    }

    protected function run(string $model, Steps $newSteps, Bug $bug, Workflow $workflow): void
    {
        try {
            $subject = $this->subjectManager->create($model);
            StepsRunner::run($newSteps, $workflow, $subject);
        } catch (Throwable $newThrowable) {
            if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                $this->bugHelper->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getReducerName()));
            }
        }
    }
}
