<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryMessageStorage;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class MessageTestCase extends TestCase
{
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
    }

    protected function consumeMessages()
    {
        /** @var InMemoryMessageStorage $messageStorage */
        $messageStorage = self::$container->get(InMemoryMessageStorage::class);

        $command = $this->application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        while (true) {
            $commandTester->execute([
                'command'  => $command->getName(),
                'receiver' => 'memory',
            ]);
            if (!$messageStorage->hasMessages()) {
                break;
            }
        }
    }
}
