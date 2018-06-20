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
     * @param string $stopCondition
     * @param string $stopConditionArguments
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $generator, string $stopCondition, string $stopConditionArguments)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator($generator);
        $task->setStopCondition($stopCondition);
        $task->setStopConditionArguments($stopConditionArguments);
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
            $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getReproducePath()->getBugMessage());
            $this->assertContains('product=49', $bugs[0]->getReproducePath()->getSteps());
            $this->assertEquals('checkout', substr($bugs[0]->getReproducePath()->getSteps(), -8));
        } else {
            $this->assertEquals(0, count($bugs));
        }
    }

    public function consumeMessageData()
    {
        return [
            ['random', 'coverage', '{"edgeCoverage":100,"vertexCoverage":100}'],
            ['random', 'max-length', '{}'],
            ['all-places', 'noop', '{}'],
            ['all-transitions', 'noop', '{}'],
        ];
    }
}
