<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Reporter;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class TaskMessageTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $generator
     * @param string $reducer
     * @param bool   $takeScreenshots
     * @param bool   $reportBug
     *
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $model, string $generator, string $reducer, bool $takeScreenshots, bool $reportBug)
    {
        $this->clearMessages();
        $this->clearReport();
        $this->removeScreenshots();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator($generator));
        $task->setReducer(new Reducer($reducer));
        $task->setTakeScreenshots($takeScreenshots);
        // Use default generator options.
        //$task->setGeneratorOptions($generatorOptions);
        if ($reportBug) {
            $task->setReporters([new Reporter('in-memory')]);
        }
        $entityManager->persist($task);
        $entityManager->flush();

        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        if (count($bugs)) {
            $this->assertEquals(1, count($bugs));
            if ('shopping_cart' === $model) {
                $ids = [];
                foreach ($bugs[0]->getPath()->getSteps() as $step) {
                    if ($step->getData() && $step->getData()->has('product')) {
                        $ids[] = $step->getData()->get('product');
                    }
                }
                if ('You added an out-of-stock product into cart! Can not checkout' === $bugs[0]->getBugMessage()) {
                    $this->assertContains(49, $ids);
                } elseif ('You need to specify options for this product! Can not add product' === $bugs[0]->getBugMessage()) {
                    $this->assertGreaterThanOrEqual(1, count(array_intersect([42, 30, 35], $ids)));
                } else {
                    $this->fail();
                }
            } elseif ('checkout' === $model) {
                $this->assertEquals('Still able to do register account, guest checkout or login when logged in!', $bugs[0]->getBugMessage());
            } elseif ('product' === $model) {
                $this->assertEquals('Can not upload file!', $bugs[0]->getBugMessage());
            }
            $this->assertEquals(0, $bugs[0]->getMessagesCount());

            $this->assertEquals($reportBug, $this->hasReport($bugs[0]));
            if ($takeScreenshots && $reportBug) {
                $this->assertTrue($this->reportHasScreenshot($bugs[0]));
            }
            $this->assertEquals(BugWorkflow::REDUCED, $bugs[0]->getStatus());

            $bugId = $bugs[0]->getId();
            if ($takeScreenshots) {
                $this->assertEquals($bugs[0]->getPath()->getLength(), $this->countScreenshots($bugId));
            } else {
                $this->assertEquals(0, $this->countScreenshots($bugId));
            }
            $entityManager->remove($bugs[0]);
            $entityManager->flush();

            $this->consumeMessages();
            $this->assertEquals(0, $this->countScreenshots($bugId));
        } else {
            $this->assertEquals(0, count($bugs));
        }
        $this->assertEquals(TaskWorkflow::COMPLETED, $task->getStatus());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'random', 'loop', true, true],
            ['shopping_cart', 'random', 'split', false, true],
            ['shopping_cart', 'random', 'random', true, false],
            ['shopping_cart', 'probability', 'loop', true, true],
            ['shopping_cart', 'all-places', 'loop', true, true],
            ['shopping_cart', 'all-transitions', 'loop', false, false],
            ['checkout', 'random', 'loop', false, false],
            ['checkout', 'random', 'split', true, true],
            ['checkout', 'random', 'random', true, true],
            ['checkout', 'probability', 'loop', false, false],
            ['product', 'random', 'loop', true, false],
            ['product', 'random', 'split', false, true],
            ['product', 'random', 'random', false, false],
            ['product', 'probability', 'loop', true, true],
        ];
    }
}
