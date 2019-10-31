<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Command\DefaultBugTitleTrait;
use Tienvx\Bundle\MbtBundle\Command\MessageTrait;
use Tienvx\Bundle\MbtBundle\Command\SubjectTrait;
use Tienvx\Bundle\MbtBundle\Command\TokenTrait;
use Tienvx\Bundle\MbtBundle\Command\WorkflowRegisterTrait;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\BugHelper;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\TestBugMessage;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class TestBugMessageHandler implements MessageHandlerInterface
{
    use TokenTrait;
    use SubjectTrait;
    use MessageTrait;
    use WorkflowRegisterTrait;
    use DefaultBugTitleTrait;

    /**
     * @var GeneratorManager
     */
    private $generatorManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @param TestBugMessage $message
     *
     * @throws Exception
     */
    public function __invoke(TestBugMessage $message)
    {
        $bugId = $message->getBugId();
        $bug = $this->entityManager->find(Bug::class, $bugId);

        if (!$bug instanceof Bug) {
            throw new Exception(sprintf('No bug found for id %d', $bugId));
        }

        if (BugWorkflow::CLOSED !== $bug->getStatus()) {
            throw new Exception(sprintf('Can not test bug with id %d, only closed bug can be tested again', $bugId));
        }

        $workflow = WorkflowHelper::get($this->workflowRegistry, $bug->getModel()->getName());
        if (WorkflowHelper::checksum($workflow) !== $bug->getModelHash()) {
            throw new Exception(sprintf('Model checksum of bug with id %d does not match', $bugId));
        }

        $subject = $this->getSubject($bug->getModel()->getName());
        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            StepsRunner::record($bug->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === $bug->getBugMessage()) {
                if ($recorded->getLength() < $bug->getSteps()->getLength()) {
                    BugHelper::updateSteps($this->entityManager, $bug, $recorded);
                }
                $this->applyBugTransition($bugId, BugWorkflow::REOPEN);
            } else {
                $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), null, $bug->getModel()->getName());
            }
        } finally {
            $subject->tearDown();
        }
    }
}
