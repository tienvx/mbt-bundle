<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\PredefinedCase;
use Tienvx\Bundle\MbtBundle\Helper\MessageHelper;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;
use Tienvx\Bundle\MbtBundle\Helper\Steps\Recorder as StepsRecorder;
use Tienvx\Bundle\MbtBundle\Message\TestPredefinedCaseMessage;
use Tienvx\Bundle\MbtBundle\PredefinedCase\PredefinedCaseManager;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
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
     * @var ModelHelper
     */
    private $modelHelper;

    /**
     * @var StepsRecorder
     */
    private $stepsRecorder;

    public function __construct(
        SubjectManager $subjectManager,
        PredefinedCaseManager $predefinedCaseManager,
        MessageHelper $messageHelper,
        ModelHelper $modelHelper,
        StepsRecorder $stepsRecorder
    ) {
        $this->subjectManager = $subjectManager;
        $this->predefinedCaseManager = $predefinedCaseManager;
        $this->messageHelper = $messageHelper;
        $this->modelHelper = $modelHelper;
        $this->stepsRecorder = $stepsRecorder;
    }

    public function __invoke(TestPredefinedCaseMessage $message): void
    {
        $name = $message->getPredefinedCase();

        if (!$this->predefinedCaseManager->has($name)) {
            throw new Exception(sprintf('No pre-defined case found for name %s', $name));
        }

        $predefinedCase = $this->predefinedCaseManager->get($name);
        $modelName = $predefinedCase->getModel()->getName();
        $subject = $this->subjectManager->createAndSetUp($modelName);

        $this->test($predefinedCase, $subject, $modelName);
    }

    protected function test(PredefinedCase $predefinedCase, SubjectInterface $subject, string $modelName): void
    {
        $recorded = new Steps();
        try {
            $model = $this->modelHelper->get($modelName);
            $this->stepsRecorder->record($predefinedCase->getSteps(), $model, $subject, $recorded);
        } catch (Throwable $throwable) {
            $this->messageHelper->createBug($recorded, $throwable->getMessage(), null, $modelName);
        } finally {
            $subject->tearDown();
        }
    }
}
