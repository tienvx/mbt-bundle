<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Message\QueuedLoopMessage;
use Tienvx\Bundle\MbtBundle\PathReducer\QueuedLoopPathReducer;

class QueuedLoopMessageHandler implements MessageHandlerInterface
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param QueuedLoopMessage $queuedLoopMessage
     * @throws Exception
     */
    public function __invoke(QueuedLoopMessage $queuedLoopMessage)
    {
        $reducer = QueuedLoopPathReducer::getName();
        $message = $queuedLoopMessage;
        $process = new Process(sprintf("bin/console mbt:handle-path-reducer %s '%s'", $reducer, $message));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->params->get('kernel.project_dir'));
        $process->disableOutput();

        $process->run();
    }
}
