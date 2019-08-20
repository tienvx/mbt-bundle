<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Helper\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractReducer implements ReducerInterface
{
    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        MessageBusInterface $messageBus,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager = $subjectManager;
        $this->messageBus = $messageBus;
        $this->graphBuilder = $graphBuilder;
        $this->entityManager = $entityManager;
    }

    public static function support(): bool
    {
        return true;
    }

    /**
     * @param Bug      $bug
     * @param Workflow $workflow
     * @param int      $length
     * @param int      $from
     * @param int      $to
     *
     * @throws Exception
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to)
    {
        $model = $bug->getTask()->getModel()->getName();
        $graph = $this->graphBuilder->build($workflow);
        $steps = $bug->getSteps();

        if ($steps->getLength() === $length) {
            // The reproduce path has not been reduced.
            $newSteps = StepsBuilder::createWithShortestPath($graph, $steps, $from, $to);
            // Make sure new path shorter than old path.
            if ($newSteps->getLength() < $steps->getLength()) {
                try {
                    $subject = $this->subjectManager->createSubject($model);
                    StepsRunner::run($newSteps, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $this->updateSteps($bug, $newSteps);
                    }
                }
            }
        }

        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }

    /**
     * @param Bug   $bug
     * @param Steps $newSteps
     *
     * @throws Throwable
     */
    protected function updateSteps(Bug $bug, Steps $newSteps)
    {
        $length = $bug->getSteps()->getLength();
        $callback = function () use ($bug, $newSteps, $length) {
            // Reload the bug for the newest messages length.
            $bug = $this->entityManager->find(Bug::class, $bug->getId(), LockMode::PESSIMISTIC_WRITE);

            if ($bug instanceof Bug && $length === $bug->getSteps()->getLength()) {
                $bug->setSteps($newSteps);
            }
        };

        $this->entityManager->transactional($callback);

        $this->messageBus->dispatch(new ReduceBugMessage($bug->getId(), static::getName()));
    }
}
