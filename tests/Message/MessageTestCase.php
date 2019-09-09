<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use App\Reporter\InMemoryReporter;
use Exception;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
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
     * @var InMemoryReporter
     */
    protected $report;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @throws Exception
     */
    protected function setUp(): void
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

        $reporterManager = self::$container->get('mbt.reporter_manager');
        $this->report = $reporterManager->getReporter('in-memory');

        $this->messageBus = self::$container->get(MessageBusInterface::class);
    }

    /**
     * @throws Exception
     */
    protected function consumeMessages()
    {
        while ($this->hasMessages()) {
            $this->runCommand('messenger:consume memory --limit=1');
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

    protected function clearReport()
    {
        $this->report->reset();
    }

    protected function hasReport(Bug $bug)
    {
        return $this->report->isReported($bug->getId());
    }

    protected function reportHasScreenshot(Bug $bug)
    {
        return $this->report->hasScreenshot($bug->getId());
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

    protected function sendMessage($message)
    {
        $this->messageBus->dispatch($message);
    }
}
