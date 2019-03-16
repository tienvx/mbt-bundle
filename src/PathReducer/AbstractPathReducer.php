<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\CaptureScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportMessage;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractPathReducer implements PathReducerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        GraphBuilder $graphBuilder
    ) {
        $this->dispatcher     = $dispatcher;
        $this->subjectManager = $subjectManager;
        $this->entityManager  = $entityManager;
        $this->messageBus     = $messageBus;
        $this->graphBuilder   = $graphBuilder;
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function finish(Bug $bug)
    {
        $message = new ReportMessage($bug->getId());
        $this->messageBus->dispatch($message);
        if ($bug->getTask()->getTakeScreenshots()) {
            $message = new CaptureScreenshotsMessage($bug->getId());
            $this->messageBus->dispatch($message);
        }
    }

    public function handle(ReductionMessage $message)
    {
    }

    /**
     * @param Bug $bug
     * @throws Exception
     */
    public function reduce(Bug $bug)
    {
        if (!$this->workflowRegistry instanceof Registry) {
            throw new Exception('Can not reduce the bug: No workflows were defined');
        }

        $messagesCount = $this->dispatch($bug->getId());
        if ($messagesCount === 0 && $bug->getStatus() !== 'reported') {
            // TODO Add 'finished' status
            $this->finish($bug);
        }
    }

    /**
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function postHandle(ReductionMessage $message)
    {
        $callback = function () use ($message) {
            $bug = $this->entityManager->find(Bug::class, $message->getBugId(), LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return;
            }

            if ($bug->getMessagesCount() > 0) {
                $bug->setMessagesCount($bug->getMessagesCount() - 1);
            }

            if ($bug->getMessagesCount() === 0) {
                $messagesCount = $this->dispatch($bug->getId(), null, $message);
                if ($messagesCount === 0 && $bug->getStatus() !== 'reported') {
                    // TODO Add 'finished' status
                    $this->finish($bug);
                }
            }
        };

        $this->entityManager->transactional($callback);
    }
}
