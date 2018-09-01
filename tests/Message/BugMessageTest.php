<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class BugMessageTest extends MessageTestCase
{
    /**
     * @param string $steps
     * @param int $length
     * @param string $reducer
     * @param string $reporter
     * @param string $expectedSteps
     * @param int $expectedLength
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $steps, int $length, string $reducer, string $reporter, string $expectedSteps, int $expectedLength)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setReducer($reducer);
        $task->setReporter($reporter);
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearHipchatMessages();

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setStatus('unverified');
        $bug->setPath($steps);
        $bug->setLength($length);
        $bug->setTask($task);
        $bug->setBugMessage('You added an out-of-stock product into cart! Can not checkout');
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->consumeMessages();

        $entityManager->refresh($bug);

        /** @var Bug[] $bugs */
        $bugs = $entityManager->getRepository(Bug::class)->findBy(['task' => $task->getId()]);

        $this->assertEquals(1, count($bugs));
        $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getBugMessage());
        if ($reducer !== 'random') {
            $this->assertEquals($expectedSteps, $bugs[0]->getPath());
            $this->assertEquals($expectedLength, $bugs[0]->getLength());
        } else {
            $this->assertLessThanOrEqual($expectedLength, $bugs[0]->getLength());
        }

        if ($reporter === 'email') {
            $command = $this->application->find('swiftmailer:spool:send');
            $commandTester = new CommandTester($command);
            $commandTester->execute([
                'command' => $command->getName(),
            ]);

            $output = $commandTester->getDisplay();
            $this->assertContains('1 emails sent', $output);
        } elseif ($reporter === 'hipchat') {
            $this->hasHipchatMessages();
        }
    }

    public function consumeMessageData()
    {
        return [
            [
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3,
                'queued-loop',
                'email',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home viewAnyCategoryFromHome(category=34) category viewProductFromCategory(product=48) product addFromProduct() product checkoutFromProduct() checkout viewCartFromCheckout() cart viewProductFromCart(product=48) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                9,
                'queued-loop',
                'hipchat',
                'home viewAnyCategoryFromHome(category=34) category viewProductFromCategory(product=48) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
            [
                'home addFromHome(product=40) home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                4,
                'greedy',
                'email',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category viewProductFromCategory(product=49) product addFromProduct() product checkoutFromProduct() checkout',
                8,
                'binary',
                'hipchat',
                'home viewAnyCategoryFromHome(category=57) category viewProductFromCategory(product=49) product addFromProduct() product checkoutFromProduct() checkout',
                4
            ],
            [
                'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout',
                7,
                'binary',
                'email',
                'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                4
            ],
            [
                'home viewCartFromHome() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category viewOtherCategory(category=25_28) category viewOtherCategory(category=20) category checkoutFromCategory() checkout',
                7,
                'greedy',
                'hipchat',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home checkoutFromHome() checkout backToHomeFromCheckout() home viewAnyCategoryFromHome(category=20) category addFromCategory(product=46) category viewProductFromCategory(product=33) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category viewCartFromCategory() cart viewProductFromCart(product=46) product viewAnyCategoryFromProduct(category=57) category checkoutFromCategory() checkout',
                11,
                'loop',
                'email',
                'home viewAnyCategoryFromHome(category=20) category viewProductFromCategory(product=33) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
            [
                'home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=24) category viewOtherCategory(category=17) category viewOtherCategory(category=24) category viewProductFromCategory(product=28) product addFromProduct() product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category viewOtherCategory(category=20_27) category viewOtherCategory(category=20) category addFromCategory(product=33) category checkoutFromCategory() checkout',
                13,
                'queued-loop',
                'hipchat',
                'home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
            [
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout',
                6,
                'greedy',
                'email',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home viewAnyCategoryFromHome(category=18) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout',
                6,
                'random',
                'hipchat',
                '',
                6
            ],
        ];
    }

    protected function clearHipchatMessages()
    {
        exec("rm -rf {$this->cacheDir}/hipchat/");
    }

    protected function hasHipchatMessages()
    {
        return filesize("{$this->cacheDir}/hipchat/message.data") !== 0;
    }
}
