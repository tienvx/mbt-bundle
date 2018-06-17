<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\TraceableMessageBus;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\StopCondition\FoundBugStopCondition;

class QueuedPathReducerMessageTest extends MessageTestCase
{
    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        /** @var TraceableMessageBus $messageBus */
        $messageBus = self::$container->get(MessageBusInterface::class);
        /** @var ContainerInterface $receiverLocator */
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /** @var FoundBugStopCondition $stopCondition */
        $stopCondition = self::$container->get(FoundBugStopCondition::class);

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setStopCondition('modified-found-bug');
        $task->setStopConditionArguments('{}');
        $task->setReducer('queued-loop');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $reproducePath = new ReproducePath();
        $reproducePath->setModel('shopping_cart');
        $reproducePath->setSteps('home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=24) category viewOtherCategory(category=17) category viewOtherCategory(category=24) category viewProductFromCategory(product=28) product addFromProduct() product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category viewOtherCategory(category=20_27) category viewOtherCategory(category=20) category addFromCategory(product=33) category checkoutFromCategory() checkout');
        $reproducePath->setLength(13);
        $reproducePath->setMessageHashes([]);
        $reproducePath->setTask($task);
        $reproducePath->setBugMessage('You added an out-of-stock product into cart! Can not checkout');
        $reproducePath->setReducer('queued-loop');
        $reproducePath->setDistance(13);
        $entityManager->persist($reproducePath);

        $entityManager->flush();

        $command = $this->application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => 'reproduce_path',
        ]);

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(ReproducePath::class);
        /** @var ReproducePath $reproducePath */
        $reproducePath = $entityRepository->findOneBy(['task' => $task->getId()]);

        while ($reproducePath->getDistance() > 0 || !empty($reproducePath->getMessageHashes())) {
            $commandTester->execute([
                'command'      => $command->getName(),
                'receiver'     => 'queued_path_reducer',
            ]);
        }

        if ($stopCondition->bugFound) {
            $reproducePath = $entityRepository->find($reproducePath->getId());
            $this->assertEquals('home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout', $reproducePath->getSteps());
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
