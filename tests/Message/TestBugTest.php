<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class TestBugTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $bugMessage
     * @param Steps  $steps
     * @param int    $bugsCount
     * @param bool   $reopen
     *
     * @throws Exception
     */
    public function setUpAndExecute(string $model, string $bugMessage, Steps $steps, int $bugsCount, bool $reopen = false)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /** @var Registry $workflowRegistry */
        $workflowRegistry = self::$container->get(Registry::class);
        $workflow = WorkflowHelper::get($workflowRegistry, $model);

        $task = new Task();
        $task->setTitle('Just dummy task');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator('random'));
        $task->setReducer(new Reducer('loop'));
        $entityManager->persist($task);

        $bug = new Bug();
        $bug->setTitle('Test regression bug');
        $bug->setSteps($steps);
        $bug->setModel(new Model($model));
        $bug->setModelHash(WorkflowHelper::checksum($workflow));
        $bug->setTask($task);
        $bug->setBugMessage($bugMessage);
        $bug->setStatus(BugWorkflow::CLOSED);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearReport();
        $this->removeScreenshots();

        $this->runCommand(sprintf('mbt:bug:test %d', $bug->getId()));
        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        $this->assertEquals($bugsCount, count($bugs));
        if ($reopen) {
            $this->assertEquals(BugWorkflow::REDUCED, $bugs[0]->getStatus());
        }
    }

    /**
     * @throws Exception
     */
    public function testFixedBug()
    {
        $bugMessage = 'Fixed bug';
        $steps = Steps::denormalize([
            ['transition' => null, 'data' => [], 'places' => ['home']],
            ['transition' => 'viewProductFromHome', 'data' => [['key' => 'product', 'value' => '40']], 'places' => ['product']],
            ['transition' => 'addFromProduct', 'data' => [], 'places' => ['product']],
            ['transition' => 'viewCartFromProduct', 'data' => [], 'places' => ['cart']],
            ['transition' => 'useCoupon', 'data' => [], 'places' => ['cart']],
        ]);
        $this->setUpAndExecute('shopping_cart', $bugMessage, $steps, 1);
    }

    /**
     * @throws Exception
     */
    public function testSameBug()
    {
        $bugMessage = 'You added an out-of-stock product into cart! Can not checkout';
        $steps = Steps::denormalize([
            ['transition' => null, 'data' => [], 'places' => ['home']],
            ['transition' => 'viewAnyCategoryFromHome', 'data' => [['key' => 'category', 'value' => '57']], 'places' => ['category']],
            ['transition' => 'addFromCategory', 'data' => [['key' => 'product', 'value' => '49']], 'places' => ['category']],
            ['transition' => 'checkoutFromCategory', 'data' => [], 'places' => ['checkout']],
        ]);
        $this->setUpAndExecute('shopping_cart', $bugMessage, $steps, 1);
    }

    /**
     * @throws Exception
     */
    public function testShorterBug()
    {
        $bugMessage = 'You added an out-of-stock product into cart! Can not checkout';
        $steps = Steps::denormalize([
            ['transition' => null, 'data' => [], 'places' => ['home']],
            ['transition' => 'viewAnyCategoryFromHome', 'data' => [['key' => 'category', 'value' => '57']], 'places' => ['category']],
            ['transition' => 'addFromCategory', 'data' => [['key' => 'product', 'value' => '49']], 'places' => ['category']],
            ['transition' => 'checkoutFromCategory', 'data' => [], 'places' => ['checkout']],
            ['transition' => 'viewOtherCategory', 'data' => [['key' => 'category', 'value' => '33']], 'places' => ['category']],
            ['transition' => 'checkoutFromCategory', 'data' => [], 'places' => ['checkout']],
        ]);
        $this->setUpAndExecute('shopping_cart', $bugMessage, $steps, 1, true);
    }

    /**
     * @throws Exception
     */
    public function testDifferentBug()
    {
        $bugMessage = 'You added an out-of-stock product into cart! Can not checkout';
        // The new bug message is: 'You need to specify options for this product! Can not add product';
        $steps = Steps::denormalize([
            ['transition' => null, 'data' => [], 'places' => ['home']],
            ['transition' => 'viewAnyCategoryFromHome', 'data' => [['key' => 'category', 'value' => '57']], 'places' => ['category']],
            ['transition' => 'addFromCategory', 'data' => [['key' => 'product', 'value' => '49']], 'places' => ['category']],
            ['transition' => 'backToHomeFromCategory', 'data' => [], 'places' => ['home']],
            ['transition' => 'addFromHome', 'data' => [['key' => 'product', 'value' => '30']], 'places' => ['home']],
            ['transition' => 'checkoutFromHome', 'data' => [], 'places' => ['checkout']],
        ]);
        $this->setUpAndExecute('shopping_cart', $bugMessage, $steps, 2);
    }
}
