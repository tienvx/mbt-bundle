<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use App\EventListener\MessageListener;
use Exception;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class MessageTestCase extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var InMemoryTransport
     */
    protected $messengerTransport;

    /**
     * @var MessageListener
     */
    protected $messageListener;

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
        $this->runCommand([
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--quiet' => true,
        ]);
        $this->runCommand([
            'command' => 'doctrine:database:create',
            '--quiet' => true,
        ]);
        $this->runCommand([
            'command' => 'doctrine:schema:create',
            '--quiet' => true,
        ]);

        $this->filesystem = self::$container->get('mbt.storage');
        $this->messengerTransport = self::$container->get('messenger.transport.memory');
        $this->messageListener = self::$container->get(MessageListener::class);
        $this->messageBus = self::$container->get(MessageBusInterface::class);
    }

    /**
     * @throws Exception
     */
    protected function consumeMessages()
    {
        while ($this->hasMessages()) {
            $this->runCommand([
                'command' => 'messenger:consume',
                'receivers' => ['memory'],
                '--limit' => 1,
                '--quiet' => true,
            ]);
        }
    }

    protected function clearMessages()
    {
        $this->messengerTransport->reset();
    }

    protected function hasMessages()
    {
        return count($this->messengerTransport->get()) > 0;
    }

    protected function clearEmails()
    {
        $this->messageListener->reset();
    }

    protected function hasEmail()
    {
        return 1 === $this->messageListener->count();
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
