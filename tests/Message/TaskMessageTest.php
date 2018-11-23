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
     * @param string $reducer
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $model, string $generator, string $reducer)
    {
        $this->clearMessages();
        $this->clearLog();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel($model);
        $task->setGenerator($generator);
        $task->setReducer($reducer);
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
                if ($bugs[0]->getBugMessage() === 'You added an out-of-stock product into cart! Can not checkout') {
                    $this->assertContains('{s:7:"product";s:2:"49";}', $bugs[0]->getPath());
                } elseif ($bugs[0]->getBugMessage() === 'You need to specify options for this product! Can not add product') {
                    if (strstr($bugs[0]->getPath(), '{s:7:"product";s:2:"42";}') === false &&
                        strstr($bugs[0]->getPath(), '{s:7:"product";s:2:"30";}') === false &&
                        strstr($bugs[0]->getPath(), '{s:7:"product";s:2:"35";}') === false
                    ) {
                        $this->fail();
                    }
                } else {
                    $this->fail();
                }
            } else {
                $this->assertEquals('Should login automatically after registering', $bugs[0]->getBugMessage());
            }
        } else {
            $this->assertEquals(0, count($bugs));
        }
        $this->assertEquals('completed', $task->getStatus());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'random', 'loop'],
            ['shopping_cart', 'random', 'binary'],
            ['shopping_cart', 'random', 'random'],
            ['shopping_cart', 'weight', 'loop'],
            ['shopping_cart', 'all-places', 'loop'],
            ['shopping_cart', 'all-transitions', 'loop'],
            ['checkout', 'random', 'loop'],
            ['checkout', 'random', 'binary'],
            ['checkout', 'random', 'random'],
            ['checkout', 'weight', 'loop'],
        ];
    }
}
