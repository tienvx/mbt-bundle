<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class MessageHelper
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var BugHelper
     */
    private $bugHelper;

    public function __construct(
        MessageBusInterface $messageBus,
        BugHelper $bugHelper
    ) {
        $this->messageBus = $messageBus;
        $this->bugHelper = $bugHelper;
    }

    public function createBug(Steps $steps, string $bugMessage, ?int $taskId, string $model): void
    {
        $message = new CreateBugMessage(
            $this->bugHelper->getDefaultBugTitle(),
            $steps->serialize(),
            $bugMessage,
            $taskId,
            BugWorkflow::NEW,
            $model
        );
        $this->messageBus->dispatch($message);
    }
}
