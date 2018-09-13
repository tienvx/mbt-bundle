<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class MessageTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        /** @var MessageBusInterface $messageBus */
        $messageBus = self::$container->get(MessageBusInterface::class);
        /** @var ContainerInterface $receiverLocator */
        $receiverLocator = self::$container->get('messenger.receiver_locator');

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        /** @var ParameterBagInterface $params */
        $this->params = self::$container->get(ParameterBagInterface::class);
        $this->cacheDir = $this->params->get('kernel.cache_dir');
        $this->clearMessages();
        $this->clearEmails();
    }

    protected function consumeMessages()
    {
        while (true) {
            $process = new Process('bin/console messenger:consume-messages filesystem --limit=1');
            $process->setTimeout(null);
            $process->setWorkingDirectory($this->params->get('kernel.project_dir'));

            $process->run();
            if (!$this->hasMessages()) {
                break;
            }
        }
    }

    protected function clearMessages()
    {
        exec("rm -rf {$this->cacheDir}/queue/");
    }

    protected function clearEmails()
    {
        exec("rm -rf {$this->cacheDir}/spool/");
    }

    protected function hasMessages()
    {
        return filesize( "{$this->cacheDir}/queue/queue.data") !== 0;
    }
}
