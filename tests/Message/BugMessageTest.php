<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Tests\Messenger\InMemoryReceiver;

class BugMessageTest extends WebTestCase
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

        $bug = new Bug();
        $bug->setTitle('Test task message');
        $bug->setMessage('shopping_cart');
        $bug->setGenerator('random');
        $bug->setArguments('{"stop":{"on":"found-bug"}}');
        $bug->setReducer('weighted-random');
        $bug->setProgress(0);
        $bug->setStatus('not-started');
        $entityManager->persist($bug);
        $entityManager->flush();

        $command = $application->find('messenger:consume-messages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'      => $command->getName(),
            'receiver'     => InMemoryReceiver::class,
        ]);

        $countBugs = $entityManager->getRepository(Bug::class)->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.task = :task_id')
            ->setParameter('task_id', $bug->getId())
            ->getQuery()
            ->getSingleScalarResult();
        $this->assertEquals(1, $countBugs);
    }
}
