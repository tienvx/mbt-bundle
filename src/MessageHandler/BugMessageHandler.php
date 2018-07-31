<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;

class BugMessageHandler implements MessageHandlerInterface
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param BugMessage $bugMessage
     * @throws \Exception
     */
    public function __invoke(BugMessage $bugMessage)
    {
        $id = $bugMessage->getId();
        $process = new Process(sprintf('bin/console mbt:reduce-steps %d', $id));
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->params->get('kernel.project_dir'));

        $process->run();
    }
}
