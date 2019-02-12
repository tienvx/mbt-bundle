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
     * @param bool $takeScreenshots
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $model, string $generator, string $reducer, bool $takeScreenshots)
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
        $task->setTakeScreenshots($takeScreenshots);
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
                    $this->assertContains('{\"product\":\"49\"}', $bugs[0]->getPath());
                } elseif ($bugs[0]->getBugMessage() === 'You need to specify options for this product! Can not add product') {
                    if (strstr($bugs[0]->getPath(), '{\"product\":\"42\"}') === false &&
                        strstr($bugs[0]->getPath(), '{\"product\":\"30\"}') === false &&
                        strstr($bugs[0]->getPath(), '{\"product\":\"35\"}') === false
                    ) {
                        $this->fail();
                    }
                } else {
                    $this->fail();
                }
            } elseif ($model === 'checkout') {
                $this->assertEquals('Still able to do register account, guest checkout or login when logged in!', $bugs[0]->getBugMessage());
            } elseif ($model === 'product') {
                $this->assertEquals('Upload required!', $bugs[0]->getBugMessage());
            }
            $this->assertEquals(0, $bugs[0]->getMessagesCount());

            $bugId = $bugs[0]->getId();
            if ($takeScreenshots) {
                $this->assertEquals($bugs[0]->getLength() - 1, $this->countScreenshots($bugId));
            } else {
                $this->assertEquals(0, $this->countScreenshots($bugId));
            }
            $entityManager->remove($bugs[0]);
            $entityManager->flush();
            $this->assertEquals(0, $this->countScreenshots($bugId));
        } else {
            $this->assertEquals(0, count($bugs));
        }
        $this->assertEquals('completed', $task->getStatus());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'random', 'loop', true],
            ['shopping_cart', 'random', 'binary', false],
            ['shopping_cart', 'random', 'random', true],
            ['shopping_cart', 'probability', 'loop', false],
            ['shopping_cart', 'all-places', 'loop', true],
            ['shopping_cart', 'all-transitions', 'loop', false],
            ['checkout', 'random', 'loop', true],
            ['checkout', 'random', 'binary', false],
            ['checkout', 'random', 'random', true],
            ['checkout', 'probability', 'loop', false],
            ['product', 'random', 'loop', true],
            ['product', 'random', 'binary', false],
            //['product', 'random', 'random', true],
            ['product', 'probability', 'loop', false],
        ];
    }
}
