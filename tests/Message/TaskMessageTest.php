<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class TaskMessageTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $generator
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $model, string $generator, string $reducer)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel($model);
        $task->setGenerator($generator);
        $task->setReducer($reducer);
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
            if ($model === 'shopping_cart') {
                $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
                $this->assertContains('{s:7:"product";s:2:"49";}', $bugs[0]->getPath());
            } else {
                $this->assertContains('has been removed after using new', $bugs[0]->getBugMessage());
            }
            $this->assertEquals('completed', $task->getStatus());
        } else {
            $this->assertEquals(0, count($bugs));
            $this->assertEquals('in-progress', $task->getStatus());
        }
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'random', 'loop'],
            ['shopping_cart', 'weighted-random', 'loop'],
            ['shopping_cart', 'all-places', 'loop'],
            ['shopping_cart', 'all-transitions', 'loop'],
            ['checkout', 'random', 'queued-loop'],
            ['checkout', 'weighted-random', 'queued-loop'],
        ];
    }
}
