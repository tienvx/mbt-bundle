<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class ApplyBugTransitionMessageHandler implements MessageHandlerInterface
{
    /**
     * @var BugWorkflow
     */
    private $bugWorkflow;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, BugWorkflow $bugWorkflow)
    {
        $this->entityManager = $entityManager;
        $this->bugWorkflow = $bugWorkflow;
    }

    /**
     * @param ApplyBugTransitionMessage $message
     *
     * @throws Exception
     */
    public function __invoke(ApplyBugTransitionMessage $message)
    {
        $bugId = $message->getId();
        $transition = $message->getTransition();
        $bug = $this->entityManager->getRepository(Bug::class)->find($bugId);

        if (!$bug || !$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        $this->bugWorkflow->apply($bug, $transition);
        $this->entityManager->flush();
    }
}
