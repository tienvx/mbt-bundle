<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Message\QueuedPathReducerMessage;
use Tienvx\Bundle\MbtBundle\PathReducer\QueuedPathReducerInterface;
use Tienvx\Bundle\MbtBundle\Service\PathReducerManager;

class QueuedPathReducerMessageHandler implements MessageHandlerInterface
{
    private $reducerManager;

    public function __construct(PathReducerManager $reducerManager)
    {
        $this->reducerManager = $reducerManager;
    }

    /**
     * @param QueuedPathReducerMessage $queuedPathReducerMessage
     * @throws \Exception
     */
    public function __invoke(QueuedPathReducerMessage $queuedPathReducerMessage)
    {
        if ($this->reducerManager->hasPathReducer($queuedPathReducerMessage->getReducer())) {
            $reducer = $this->reducerManager->getPathReducer($queuedPathReducerMessage->getReducer());
            if ($reducer instanceof QueuedPathReducerInterface) {
                $reducer->handle($queuedPathReducerMessage);
            }
        }
    }
}
