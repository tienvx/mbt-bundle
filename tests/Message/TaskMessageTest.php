<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class TaskMessageTest extends MessageTestCase
{
    /**
     * @param string $generator
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $generator)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator($generator);
        $task->setReducer('weighted-random');
        $task->setReporter('email');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);
        $entityManager->flush();

        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        if (count($bugs)) {
            $this->assertEquals(1, count($bugs));
            $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
            $this->assertContains('product=49', $bugs[0]->getSteps());
            $this->assertEquals('checkout', substr($bugs[0]->getSteps(), -8));
        } else {
            $this->assertEquals(0, count($bugs));
        }
    }

    public function consumeMessageData()
    {
        return [
            ['random'],
            ['weighted-random'],
            ['all-places'],
            ['all-transitions'],
        ];
    }
}
