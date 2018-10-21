<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class MessageTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $logDir;

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        /** @var ParameterBagInterface $params */
        $params = self::$container->get(ParameterBagInterface::class);
        $this->cacheDir = $params->get('kernel.cache_dir');
        $this->logDir = $params->get('kernel.logs_dir');
        $this->clearMessages();
        $this->clearEmails();
    }

    /**
     * @throws \Exception
     */
    protected function consumeMessages()
    {
        while (true) {
            $this->runCommand('messenger:consume-messages filesystem --limit=1');

            // Fix filesystem's receiver not getting messages on the next run
            $transport = self::$container->get('messenger.transport.filesystem');
            $rTransport = new \ReflectionObject($transport);
            $refReceiver = $rTransport->getProperty('receiver');
            $refReceiver->setAccessible(true);
            $receiver = $refReceiver->getValue($transport);
            $rReceiver = new \ReflectionObject($receiver);
            $refShouldStop = $rReceiver->getProperty('shouldStop');
            $refShouldStop->setAccessible(true);
            $refShouldStop->setValue($receiver, false);

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
        exec("rm -rf {$this->cacheDir}/spool/default/*");
    }

    protected function hasMessages()
    {
        // filesize is not working correctly on empty file
        $queue = file_get_contents("{$this->cacheDir}/queue/queue.data");
        return strlen($queue) !== 0;
    }

    protected function clearLog()
    {
        exec("rm -f {$this->logDir}/test.log");
    }

    protected function hasLog()
    {
        return filesize("{$this->logDir}/test.log") !== 0;
    }
}
