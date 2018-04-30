<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryReceiver;

class TaskMessageTest extends CommandTestCase
{
    public function testExecute()
    {
        $kernel = static::bootKernel();

        $messageBus = self::$container->get(MessageBusInterface::class);
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $application->run(new StringInput('doctrine:database:drop --force'));
        $application->run(new StringInput('doctrine:database:create'));
        $application->run(new StringInput('doctrine:schema:create'));

        $task = new Task();
        $task->setTitle('Test task message');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setArguments('{"stop":{"on":"found-bug"}}');
        $task->setReducer('weighted-random');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);
        $entityManager->flush();

        $command = $application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => InMemoryReceiver::class//'messenger.default_receiver',
        ]);

        $this->assertEquals(1, $task->getBugs()->count());
    }
}
