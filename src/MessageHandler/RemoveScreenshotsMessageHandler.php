<?php

namespace Tienvx\Bundle\MbtBundle\MessageHandler;

use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;

class RemoveScreenshotsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * @param RemoveScreenshotsMessage $message
     * @throws Exception
     */
    public function __invoke(RemoveScreenshotsMessage $message)
    {
        $bugId = $message->getBugId();
        $model = $message->getModel();
        $this->commandRunner->run(['mbt:bug:remove-screenshots', $bugId, $model]);
    }
}
