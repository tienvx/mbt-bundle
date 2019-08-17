<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class TestPredefinedCaseTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $name
     * @param string $generator
     * @param string $reducer
     *
     * @throws Exception
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $model, string $name, string $generator, string $reducer)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setPredefinedCase($name);

        $task = new Task();
        $task->setTitle('Test pre-defined case task');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator($generator));
        $task->setGeneratorOptions($generatorOptions);
        $task->setReducer(new Reducer($reducer));
        $task->setTakeScreenshots(false);
        $entityManager->persist($task);
        $entityManager->flush();

        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        $this->assertEquals(1, count($bugs));
        $this->assertEquals(TaskWorkflow::COMPLETED, $task->getStatus());
        $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'checkout_out_of_stock', 'test-predefined-case', 'loop'],
        ];
    }
}