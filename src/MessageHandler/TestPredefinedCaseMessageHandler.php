<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\PredefinedCase;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Steps\StepsRecorder;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

class TestPredefinedCaseMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SubjectManager
     */
    private $subjectManager;

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
    private $workflowHelper;

    public function __construct(
        SubjectManager $subjectManager,
        PredefinedCaseManager $predefinedCaseManager,
        MessageHelper $messageHelper,
        TokenHelper $tokenHelper,
        WorkflowHelper $workflowHelper
    ) {
        $this->subjectManager = $subjectManager;
        $this->predefinedCaseManager = $predefinedCaseManager;
        $this->messageHelper = $messageHelper;
        $this->tokenHelper = $tokenHelper;
        $this->workflowHelper = $workflowHelper;
    }

    public function __invoke(TestPredefinedCaseMessage $message): void
    {
        $name = $message->getPredefinedCase();

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        $model = $predefinedCase->getModel()->getName();
        $workflow = $this->workflowHelper->get($model);
        $subject = $this->subjectManager->createAndSetUp($model);

        $this->test($predefinedCase, $workflow, $subject, $model);
    }

    protected function test(PredefinedCase $predefinedCase, Workflow $workflow, SubjectInterface $subject, string $model): void
    {
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
