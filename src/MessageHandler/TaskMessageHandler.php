<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskMessageHandler implements MessageHandlerInterface
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param TaskMessage $taskMessage
     * @throws \Exception
     */
    public function __invoke(TaskMessage $taskMessage)
    {
        $id = $taskMessage->getId();
        $process = new Process(sprintf('bin/console mbt:execute-task %d', $id));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->params->get('kernel.project_dir'));

        $process->run();
    }
}
