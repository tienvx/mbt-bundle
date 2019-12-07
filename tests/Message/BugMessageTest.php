<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Reporter;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;

class BugMessageTest extends MessageTestCase
{
    /**
     * @dataProvider consumeMessageData
     *
     * @throws Exception
     */
    public function testExecute(string $model, array $steps, string $reducer, array $expectedSteps)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        /** @var WorkflowHelper $workflowHelper */
        $workflowHelper = self::$container->get(WorkflowHelper::class);
        $workflow = $workflowHelper->get($model);

        $steps = Steps::denormalize($steps);
        $expectedSteps = Steps::denormalize($expectedSteps);
        switch ($model) {
            case 'shopping_cart':
                $bugMessage = 'You added an out-of-stock product into cart! Can not checkout';
                break;
            case 'checkout':
                $bugMessage = 'Still able to do register account, guest checkout or login when logged in!';
                break;
            case 'product':
                $bugMessage = 'Can not upload file!';
                break;
            default:
                // Make PHP happy
                $bugMessage = '';
                break;
        }

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator('random'));
        $task->setReducer(new Reducer($reducer));
        $task->setTakeScreenshots(false);
        $task->setReporters([new Reporter('email')]);
        // Does not matter, because we are testing reducer.
        //$task->setGeneratorOptions($generatorOptions);
        $entityManager->persist($task);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearEmails();
        // Just to make sure
        $this->removeScreenshots();

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setSteps($steps);
        $bug->setModel(new Model($model));
        $bug->setModelHash($workflowHelper->checksum($workflow));
        $bug->setTask($task);
        $bug->setBugMessage($bugMessage);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->consumeMessages();

        $bug = $entityManager->getRepository(Bug::class)->findOneBy(['id' => $bug->getId()]);

        /** @var Bug[] $bugs */
        $bugs = $entityManager->getRepository(Bug::class)->findBy(['task' => $task->getId()]);

        $this->assertEquals(1, count($bugs));
        $this->assertEquals($bugMessage, $bugs[0]->getBugMessage());
        if ('random' !== $reducer) {
            $this->assertEquals($expectedSteps->serialize(), $bugs[0]->getSteps()->serialize());
            $this->assertEquals($expectedSteps->getLength(), $bugs[0]->getSteps()->getLength());
        } else {
            $this->assertLessThanOrEqual($expectedSteps->getLength(), $bugs[0]->getSteps()->getLength());
        }

        $this->assertTrue($this->hasEmail());
        $this->assertEquals(BugWorkflow::REDUCED, $bug->getStatus());

        // Because screenshots had not been captured, and had been removed during set-up, no need to test this
        //$this->assertTrue($this->reportHasScreenshot());
    }

    public function consumeMessageData()
    {
        $files = [
            '0.shopping-cart.split.json',
            '1.shopping-cart.loop.json',
            '2.shopping-cart.split.json',
            '3.shopping-cart.loop.json',
            '4.shopping-cart.split.json',
            '5.shopping-cart.loop.json',
            '6.shopping-cart.loop.json',
            '7.shopping-cart.loop.json',
            '8.shopping-cart.split.json',
            '9.shopping-cart.split.json',
            '10.checkout.loop.json',
            '11.shopping-cart.random.json',
            '12.product.transition.json',
        ];

        return array_map(function (string $file) {
            return json_decode(file_get_contents(__DIR__."/bugs/{$file}"), true);
        }, $files);
    }
}
