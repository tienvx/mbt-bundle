<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\GraphHelper;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy\ShortestPathStrategy;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Steps\StepsRunner;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractReducer implements ReducerInterface
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
    private $bugHelper;

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

    public static function support(): bool
    {
        return true;
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to): void
    {
        $model = $bug->getModel()->getName();
        $graph = $this->graphHelper->build($workflow);
        $steps = $bug->getSteps();

        if ($steps->getLength() !== $length) {
            // The reproduce path has been reduced.
            return;
        }

        $stepsBuilder = new StepsBuilder();
        $stepsBuilder->setStrategy(new ShortestPathStrategy($graph));
        $newSteps = $stepsBuilder->create($steps, $from, $to);
        if ($newSteps->getLength() >= $steps->getLength()) {
            // New path is longer than or equals old path.
            return;
        }

        $this->run($model, $newSteps, $bug, $workflow);
    }

    protected function run(string $model, Steps $newSteps, Bug $bug, Workflow $workflow): void
    {
        try {
            $subject = $this->subjectManager->create($model);
            StepsRunner::run($newSteps, $workflow, $subject);
        } catch (Throwable $newThrowable) {
            if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                $this->bugHelper->updateSteps($bug, $newSteps);
                $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getName()));
            }
        }
    }

    /**
     * @throws Exception
     */
    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();

        $pairs = $this->getPairs($steps);

        foreach ($pairs as $pair) {
            $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $pair[0], $pair[1]);
            $this->messageBus->dispatch($message);
        }

        return count($pairs);
    }

    protected function getPairs(Steps $steps): array
    {
        return [];
    }
}
