<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Exception;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class MessageTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $logDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var InMemoryTransport
     */
    protected $transport;

    /**
     * @throws Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        /** @var ParameterBagInterface $params */
        $params = self::$container->get(ParameterBagInterface::class);
        $this->logDir = $params->get('kernel.logs_dir');

        $this->filesystem = self::$container->get('mbt.storage');

        /** @var ContainerInterface $receiverLocator */
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        $this->transport = $receiverLocator->get('memory');
    }

    /**
     * @throws Exception
     */
    protected function consumeMessages()
    {
        while (true) {
            $this->runCommand('messenger:consume memory --limit=1');

            if (!$this->hasMessages()) {
                break;
            }
        }
    }

    protected function clearMessages()
    {
        $this->transport->reset();
    }

    protected function hasMessages()
    {
        return count($this->transport->get()) > 0;
    }

    protected function clearLog()
    {
        exec("rm -f {$this->logDir}/test.log");
    }

    protected function hasLog()
    {
        return file_exists("{$this->logDir}/test.log") && 0 !== filesize("{$this->logDir}/test.log");
    }

    protected function logHasScreenshot()
    {
        return false !== strpos(file_get_contents("{$this->logDir}/test.log"), 'Screenshot');
    }

    protected function removeScreenshots()
    {
        $contents = $this->filesystem->listContents('', true);
        foreach ($contents as $object) {
            try {
                $this->filesystem->delete($object['path']);
            } catch (FileNotFoundException $e) {
            }
        }
    }

    protected function countScreenshots(int $bugId)
    {
        return count($this->filesystem->listContents("$bugId/", false));
    }
}
