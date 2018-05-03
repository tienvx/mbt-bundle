<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryBugReceiver;

class BugMessageTest extends AbstractTestCase
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
        $task->setArguments('{"stop":{"on":"found-bug"}}');
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
            'receiver'     => InMemoryBugReceiver::class,
        ]);

        /** @var \Swift_Plugins_MessageLogger $messageLogger */
        $messageLogger = self::$container->get('swiftmailer.mailer.default.plugin.messagelogger');

        // checks that an email was sent
        $this->assertSame(1, $messageLogger->countMessages());

        $collectedMessages = $messageLogger->getMessages();
        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Test bug title', $message->getSubject());
        $this->assertSame('send@example.com', key($message->getFrom()));
        $this->assertSame('recipient@example.com', key($message->getTo()));
        $this->assertContains(
            'Bug Found',
            $message->getBody()
        );
    }
}
