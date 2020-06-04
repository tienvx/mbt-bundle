<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\PredefinedCase;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;
use Tienvx\Bundle\MbtBundle\Model\Subject\TearDownInterface;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
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
     * @var WorkflowHelper
     */
    private $workflowHelper;

    /**
     * @var StepsRecorder
     */
    private $stepsRecorder;

    public function __construct(
        SubjectManager $subjectManager,
        PredefinedCaseManager $predefinedCaseManager,
        MessageHelper $messageHelper,
        WorkflowHelper $workflowHelper,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->predefinedCaseManager = $predefinedCaseManager;
        $this->messageHelper = $messageHelper;
        $this->workflowHelper = $workflowHelper;
        $this->stepsRecorder = $stepsRecorder;
    }

    public function __invoke(TestPredefinedCaseMessage $message): void
    {
        $name = $message->getPredefinedCase();

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        $workflowName = $predefinedCase->getWorkflow()->getName();
        $subject = $this->subjectManager->createAndSetUp($workflowName);

        $this->test($predefinedCase, $subject, $workflowName);
    }

    protected function test(PredefinedCase $predefinedCase, SubjectInterface $subject, string $workflowName): void
    {
        $recorded = new Steps();
        try {
            $workflow = $this->workflowHelper->get($workflowName);
            $this->stepsRecorder->record($predefinedCase->getSteps(), $workflow, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), null, $workflowName);
        } finally {
            if ($subject instanceof TearDownInterface) {
                $subject->tearDown();
            }
        }
    }
}
