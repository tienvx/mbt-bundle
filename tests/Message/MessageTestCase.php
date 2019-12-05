<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use App\Mailer\InMemoryTransportFactory as MailerInMemoryTransportFactory;
use Exception;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport as MessengerInMemoryTransport;
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
     * @var MessengerInMemoryTransport
     */
    protected $messengerTransport;

    /**
     * @var MailerInMemoryTransportFactory
     */
    protected $mailerTransportFactory;

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

        /** @var ParameterBagInterface $params */
        $params = self::$container->get(ParameterBagInterface::class);
        $this->logDir = $params->get('kernel.logs_dir');

        $this->filesystem = self::$container->get('mbt.storage');
        $this->messengerTransport = self::$container->get('messenger.transport.memory');
        $this->mailerTransportFactory = self::$container->get(MailerInMemoryTransportFactory::class);
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
        $this->mailerTransportFactory->reset();
    }

    protected function hasEmail()
    {
        return 1 === $this->mailerTransportFactory->count();
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
