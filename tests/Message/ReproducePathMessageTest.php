<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Messenger\Message\QueuedPathReducerMessage;
use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryReproducePathReceiver;

class ReproducePathMessageTest extends AbstractTestCase
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

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $this->runCommand('doctrine:database:drop --force');
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setStopCondition('found-bug');
        $task->setStopConditionArguments('{}');
        $task->setReducer('queued-loop');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $reproducePath = new ReproducePath();
        $reproducePath->setModel('shopping_cart');
        $reproducePath->setSteps('home viewAnyCategoryFromHome(category=34) category viewProductFromCategory(product=48) product addFromProduct() product checkoutFromProduct() checkout viewCartFromCheckout() cart viewProductFromCart(product=48) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout');
        $reproducePath->setLength(9);
        $reproducePath->setTotalMessages(0);
        $reproducePath->setHandledMessages(0);
        $reproducePath->setTask($task);
        $reproducePath->setBugMessage('You added an out-of-stock product into cart! Can not checkout');
        $reproducePath->setReducer('queued-loop');
        $entityManager->persist($reproducePath);

        $entityManager->flush();

        $command = $this->application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => InMemoryReproducePathReceiver::class,
        ]);

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(ReproducePath::class);
        /** @var ReproducePath $reproducePath */
        $reproducePath = $entityRepository->find($reproducePath->getId());
        $this->assertEquals(10, $reproducePath->getTotalMessages());
        $this->assertEquals(10, count(array_filter($messageBus->getDispatchedMessages(), function (array $message) {
            return $message['message'] instanceof  QueuedPathReducerMessage;
        })));
    }
}
