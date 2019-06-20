<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\StaticCase;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Path;

class TestStaticCaseTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $generator
     * @param string $reducer
     *
     * @throws Exception
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $model, string $generator, string $reducer)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $path = new Path([
            [null, null, ['home']],
            ['addFromHome', ['product' => 40], ['home']],
            ['viewAnyCategoryFromHome', ['category' => 57], ['category']],
            ['addFromCategory', ['product' => 49], ['category']],
            ['viewCartFromCategory', [], ['cart']],
            ['update', ['product' => 49], ['cart']],
            ['remove', ['product' => 40], ['cart']],
            ['checkoutFromCart', [], ['checkout']],
        ]);

        $staticCase = new StaticCase();
        $staticCase->setModel(new Model($model));
        $staticCase->setTitle('Test checkout out-of-stock product');
        $staticCase->setPath($path);
        $entityManager->persist($staticCase);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearReport();
        $this->removeScreenshots();

        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setStaticCaseId($staticCase->getId());

        $task = new Task();
        $task->setTitle('Test static case task');
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
        $this->assertEquals('completed', $task->getStatus());
        $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'test-static-case', 'loop'],
        ];
    }
}
