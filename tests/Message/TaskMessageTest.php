<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryTaskReceiver;

class TaskMessageTest extends AbstractTestCase
{
    public function testExecute()
    {
        $messageBus = self::$container->get(MessageBusInterface::class);
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setArguments('{"stop":{"on":"found-bug"}}');
        $task->setReducer('weighted-random');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);
        $entityManager->flush();

        $command = $this->application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => InMemoryTaskReceiver::class,
        ]);

        $countBugs = $entityManager->getRepository(Bug::class)->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.task = :task_id')
            ->setParameter('task_id', $task->getId())
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertEquals(1, $countBugs);
    }
}
