<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryTaskReceiver;
use Tienvx\Bundle\MbtBundle\Tests\StopCondition\FoundBugStopCondition;

class TaskMessageTest extends AbstractTestCase
{
    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        /** @var MessageBusInterface $messageBus */
        $messageBus = self::$container->get(MessageBusInterface::class);
        /** @var ContainerInterface $receiverLocator */
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /** @var FoundBugStopCondition $stopCondition */
        $stopCondition = self::$container->get(FoundBugStopCondition::class);

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setArguments('{"stop":{"on":"modified-found-bug"}}');
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

        if ($stopCondition->bugFound) {
            /** @var EntityRepository $entityRepository */
            $entityRepository = $entityManager->getRepository(Bug::class);
            $countBugs = $entityRepository->createQueryBuilder('b')
                ->select('count(b.id)')
                ->where('b.task = :task_id')
                ->setParameter('task_id', $task->getId())
                ->getQuery()
                ->getSingleScalarResult();
            $this->assertEquals(1, $countBugs);
        }
        else {
            $this->addToAssertionCount(1);
        }
    }
}
