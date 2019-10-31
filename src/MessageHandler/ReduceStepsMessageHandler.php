<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Command\MessageTrait;
use Tienvx\Bundle\MbtBundle\Command\TokenTrait;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;

class ReduceStepsMessageHandler implements MessageHandlerInterface
{
    use TokenTrait;
    use WorkflowRegisterTrait;
    use MessageTrait;

    /**
     * @var ReducerManager
     */
    private $reducerManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        ReducerManager $reducerManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->reducerManager = $reducerManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @param ReduceStepsMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ReduceStepsMessage $message)
    {
        $bugId = $message->getBugId();
        $reducer = $message->getReducer();
        $length = $message->getLength();
        $from = $message->getFrom();
        $to = $message->getTo();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $this->setAnonymousToken();

        $reducerService = $this->reducerManager->getReducer($reducer);
        $reducerService->handle($bug, $workflow, $length, $from, $to);

        $this->finishReduceSteps($bug);
    }
}
