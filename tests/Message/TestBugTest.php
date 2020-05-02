<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Workflow;
use Tienvx\Bundle\MbtBundle\Message\TestBugMessage;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class TestBugTest extends MessageTestCase
{
    /**
     * @throws Exception
     */
    public function setUpAndExecute(string $workflow, string $bugMessage, Steps $steps, int $bugsCount, bool $reopen = false)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        $checksum = json_decode(file_get_contents(__DIR__.'/../app/var/checksum.json'), true);

        $task = new Task();
        $task->setTitle('Just dummy task');
        $task->setWorkflow(new Workflow($workflow));
        $task->setGenerator(new Generator('random'));
        $task->setReducer(new Reducer('loop'));
        $entityManager->persist($task);

        $bug = new Bug();
        $bug->setTitle('Test regression bug');
        $bug->setSteps($steps);
        $bug->setWorkflow(new Workflow($workflow));
        $bug->setWorkflowHash($checksum[$workflow]);
        $bug->setTask($task);
        $bug->setBugMessage($bugMessage);
        $bug->setStatus(BugWorkflow::CLOSED);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearEmails();
        $this->removeScreenshots();

        $this->sendMessage(new TestBugMessage($bug->getId()));
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
