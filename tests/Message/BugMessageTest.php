<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
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
        $task->setStopCondition('found-bug');
        $task->setStopConditionArguments('{}');
        $task->setReducer('greedy');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setMessage('Test bug message');
        $bug->setStatus('unverified');
        $bug->setSteps('home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout');
        $bug->setReporter('email');
        $bug->setTask($task);
        $entityManager->persist($bug);
        $entityManager->flush();

        $command = $this->application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => 'bug',
        ]);

        $command = $this->application->find('swiftmailer:spool:send');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('1 emails sent', $output);
    }
}
