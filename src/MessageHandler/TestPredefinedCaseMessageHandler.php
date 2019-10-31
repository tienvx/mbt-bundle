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
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestPredefinedCaseMessageHandler implements MessageHandlerInterface
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

    /**
     * @var PredefinedCaseManager
     */
    private $predefinedCaseManager;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        PredefinedCaseManager $predefinedCaseManager
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->predefinedCaseManager = $predefinedCaseManager;
    }

    /**
     * @param TestPredefinedCaseMessage $message
     *
     * @throws Exception
     */
    public function __invoke(TestPredefinedCaseMessage $message)
    {
        $name = $message->getPredefinedCase();

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        $model = $predefinedCase->getModel()->getName();
        $workflow = WorkflowHelper::get($this->workflowRegistry, $model);
        $subject = $this->getSubject($model);

        $this->setAnonymousToken();

        $recorded = new Steps();
        try {
            StepsRunner::record($predefinedCase->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->createBug($this->defaultBugTitle, $recorded, $throwable->getMessage(), null, $model);
        } finally {
            $subject->tearDown();
        }
    }
}
