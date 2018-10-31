<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Throwable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Message\ReductionMessage;
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

    public function __construct(
        EventDispatcherInterface $dispatcher,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->dispatcher       = $dispatcher;
        $this->subjectManager   = $subjectManager;
        $this->entityManager    = $entityManager;
        $this->messageBus       = $messageBus;
    }

    public function setWorkflowRegistry(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    protected function finish(Bug $bug)
    {
        $event = new ReducerFinishEvent($bug->getId());

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);

        $bug->getTask()->setStatus('completed');
        $this->entityManager->flush();
    }

    /**
     * @param Bug $bug
     * @param Path $path
     * @throws Exception
     */
    protected function updatePath(Bug $bug, Path $path)
    {
        $bug->setPath(serialize($path));
        $bug->setLength($path->countPlaces());
        $this->entityManager->flush();
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

        $this->dispatch($bug->getId());
    }

    /**
     * @param ReductionMessage $message
     * @throws Exception
     */
    public function postHandle(ReductionMessage $message)
    {
        $this->entityManager->beginTransaction();
        try {
            $bug = $this->entityManager->find(Bug::class, $message->getBugId(), LockMode::PESSIMISTIC_WRITE);

            if (!$bug || !$bug instanceof Bug) {
                return;
            }

            $bug->setMessagesCount($bug->getMessagesCount() - 1);
            $this->entityManager->flush();
            $this->entityManager->commit();

            if ($bug->getMessagesCount() === 0) {
                $messagesCount = $this->dispatch($bug->getId(), null, $message);
                if ($messagesCount === 0) {
                    $this->finish($bug);
                }
            }
        } catch (Throwable $throwable) {
            // Something happen, ignoring.
            $this->entityManager->rollBack();
        }
    }
}
