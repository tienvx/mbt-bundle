<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsRecorder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestPredefinedCaseMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

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

    /**
     * @var MessageHelper
     */
    private $messageHelper;

    /**
     * @var TokenHelper
     */
    private $tokenHelper;

    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;

    public function __construct(
        SubjectManager $subjectManager,
        GeneratorManager $generatorManager,
        EntityManagerInterface $entityManager,
        PredefinedCaseManager $predefinedCaseManager,
        MessageHelper $messageHelper,
        TokenHelper $tokenHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->subjectManager = $subjectManager;
        $this->generatorManager = $generatorManager;
        $this->entityManager = $entityManager;
        $this->predefinedCaseManager = $predefinedCaseManager;
        $this->messageHelper = $messageHelper;
        $this->tokenHelper = $tokenHelper;
        $this->workflowHelper = $workflowHelper;
    }

    /**
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
        $workflow = $this->workflowHelper->get($model);
        $subject = $this->subjectManager->createAndSetUp($model);

        $this->tokenHelper->setAnonymousToken();

        $recorded = new Steps();
        try {
            StepsRecorder::record($predefinedCase->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), null, $model);
        } finally {
            $subject->tearDown();
        }
    }
}
