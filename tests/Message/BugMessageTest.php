<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class BugMessageTest extends MessageTestCase
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

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setStopCondition('max-length');
        $task->setStopConditionArguments('{}');
        $task->setReducer('greedy');
        $task->setReporter('email');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $reproducePath = new ReproducePath();
        $reproducePath->setSteps('home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout');
        $reproducePath->setLength(7);
        $reproducePath->setTask($task);
        $reproducePath->setBugMessage('Test bug message');
        $entityManager->persist($reproducePath);

        $entityManager->flush();

        $this->clearMessages();

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setStatus('unverified');
        $bug->setReproducePath($reproducePath);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->consumeMessages();

        $command = $this->application->find('swiftmailer:spool:send');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('1 emails sent', $output);
    }
}
