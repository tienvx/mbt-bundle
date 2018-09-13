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
    public function testConsumeMessage(string $model, string $generator)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel($model);
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
            if ($model === 'shopping_cart') {
                $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
                $this->assertContains('{s:7:"product";s:2:"49";}', $bugs[0]->getPath());
            } else {
                $this->assertContains('has been removed after using new', $bugs[0]->getBugMessage());
            }
        } else {
            $this->assertEquals(0, count($bugs));
        }
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'random'],
            ['shopping_cart', 'weighted-random'],
            ['shopping_cart', 'all-places'],
            ['shopping_cart', 'all-transitions'],
            ['checkout', 'random'],
            ['checkout', 'weighted-random'],
        ];
    }
}
