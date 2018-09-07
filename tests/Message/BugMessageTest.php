<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class BugMessageTest extends MessageTestCase
{
    /**
     * @param string $path
     * @param int $length
     * @param string $reducer
     * @param string $reporter
     * @param string $expectedPath
     * @param int $expectedLength
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $path, int $length, string $reducer, string $reporter, string $expectedPath, int $expectedLength)
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
        $bug->setPath($path);
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
            $this->assertEquals($expectedPath, $bugs[0]->getPath());
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
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":331:{a:3:{i:0;a:4:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:15:"addFromCategory";i:3;s:20:"checkoutFromCategory";}i:1;a:4:{i:0;N;i:1;a:1:{s:8:"category";s:2:"57";}i:2;a:1:{s:7:"product";s:2:"49";}i:3;a:0:{}}i:2;a:4:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"checkout";}}}}',
                4,
                'queued-loop',
                'email',
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":331:{a:3:{i:0;a:4:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:15:"addFromCategory";i:3;s:20:"checkoutFromCategory";}i:1;a:4:{i:0;N;i:1;a:1:{s:8:"category";s:2:"57";}i:2;a:1:{s:7:"product";s:2:"49";}i:3;a:0:{}}i:2;a:4:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"checkout";}}}}',
                4
            ],
            [
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":824:{a:3:{i:0;a:10:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:23:"viewProductFromCategory";i:3;s:14:"addFromProduct";i:4;s:19:"checkoutFromProduct";i:5;s:20:"viewCartFromCheckout";i:6;s:19:"viewProductFromCart";i:7;s:26:"viewAnyCategoryFromProduct";i:8;s:15:"addFromCategory";i:9;s:20:"checkoutFromCategory";}i:1;a:10:{i:0;N;i:1;a:1:{s:8:"category";s:2:"34";}i:2;a:1:{s:7:"product";s:2:"48";}i:3;a:0:{}i:4;a:0:{}i:5;a:0:{}i:6;a:1:{s:7:"product";s:2:"48";}i:7;a:1:{s:8:"category";s:2:"57";}i:8;a:1:{s:7:"product";s:2:"49";}i:9;a:0:{}}i:2;a:10:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:7:"product";}i:3;a:1:{i:0;s:7:"product";}i:4;a:1:{i:0;s:8:"checkout";}i:5;a:1:{i:0;s:4:"cart";}i:6;a:1:{i:0;s:7:"product";}i:7;a:1:{i:0;s:8:"category";}i:8;a:1:{i:0;s:8:"category";}i:9;a:1:{i:0;s:8:"checkout";}}}}',
                10,
                'queued-loop',
                'hipchat',
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":528:{a:3:{i:0;a:6:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:23:"viewProductFromCategory";i:3;s:26:"viewAnyCategoryFromProduct";i:4;s:15:"addFromCategory";i:5;s:20:"checkoutFromCategory";}i:1;a:6:{i:0;N;i:1;a:1:{s:8:"category";s:2:"34";}i:2;a:1:{s:7:"product";s:2:"48";}i:3;a:1:{s:8:"category";s:2:"57";}i:4;a:1:{s:7:"product";s:2:"49";}i:5;a:0:{}}i:2;a:6:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:7:"product";}i:3;a:1:{i:0;s:8:"category";}i:4;a:1:{i:0;s:8:"category";}i:5;a:1:{i:0;s:8:"checkout";}}}}',
                6
            ],
            [
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":412:{a:3:{i:0;a:5:{i:0;N;i:1;s:11:"addFromHome";i:2;s:23:"viewAnyCategoryFromHome";i:3;s:15:"addFromCategory";i:4;s:20:"checkoutFromCategory";}i:1;a:5:{i:0;N;i:1;a:1:{s:7:"product";s:2:"40";}i:2;a:1:{s:8:"category";s:2:"57";}i:3;a:1:{s:7:"product";s:2:"49";}i:4;a:0:{}}i:2;a:5:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:4:"home";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"category";}i:4;a:1:{i:0;s:8:"checkout";}}}}',
                5,
                'greedy',
                'email',
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":331:{a:3:{i:0;a:4:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:15:"addFromCategory";i:3;s:20:"checkoutFromCategory";}i:1;a:4:{i:0;N;i:1;a:1:{s:8:"category";s:2:"57";}i:2;a:1:{s:7:"product";s:2:"49";}i:3;a:0:{}}i:2;a:4:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"checkout";}}}}',
                4
            ],
            [
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":720:{a:3:{i:0;a:9:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:15:"addFromCategory";i:3;s:20:"viewCartFromCategory";i:4;s:18:"backToHomeFromCart";i:5;s:23:"viewAnyCategoryFromHome";i:6;s:23:"viewProductFromCategory";i:7;s:14:"addFromProduct";i:8;s:19:"checkoutFromProduct";}i:1;a:9:{i:0;N;i:1;a:1:{s:8:"category";s:2:"33";}i:2;a:1:{s:7:"product";s:2:"31";}i:3;a:0:{}i:4;a:0:{}i:5;a:1:{s:8:"category";s:2:"57";}i:6;a:1:{s:7:"product";s:2:"49";}i:7;a:0:{}i:8;a:0:{}}i:2;a:9:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:4:"cart";}i:4;a:1:{i:0;s:4:"home";}i:5;a:1:{i:0;s:8:"category";}i:6;a:1:{i:0;s:7:"product";}i:7;a:1:{i:0;s:7:"product";}i:8;a:1:{i:0;s:8:"checkout";}}}}',
                9,
                'binary',
                'hipchat',
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":401:{a:3:{i:0;a:5:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:23:"viewProductFromCategory";i:3;s:14:"addFromProduct";i:4;s:19:"checkoutFromProduct";}i:1;a:5:{i:0;N;i:1;a:1:{s:8:"category";s:2:"57";}i:2;a:1:{s:7:"product";s:2:"49";}i:3;a:0:{}i:4;a:0:{}}i:2;a:5:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:7:"product";}i:3;a:1:{i:0;s:7:"product";}i:4;a:1:{i:0;s:8:"checkout";}}}}',
                5
            ],
            [
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":675:{a:3:{i:0;a:8:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:17:"viewOtherCategory";i:3;s:15:"addFromCategory";i:4;s:17:"viewOtherCategory";i:5;s:23:"viewProductFromCategory";i:6;s:21:"backToHomeFromProduct";i:7;s:16:"checkoutFromHome";}i:1;a:8:{i:0;N;i:1;a:1:{s:8:"category";s:2:"34";}i:2;a:1:{s:8:"category";s:2:"57";}i:3;a:1:{s:7:"product";s:2:"49";}i:4;a:1:{s:8:"category";s:2:"34";}i:5;a:1:{s:7:"product";s:2:"48";}i:6;a:0:{}i:7;a:0:{}}i:2;a:8:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"category";}i:4;a:1:{i:0;s:8:"category";}i:5;a:1:{i:0;s:7:"product";}i:6;a:1:{i:0;s:4:"home";}i:7;a:1:{i:0;s:8:"checkout";}}}}',
                8,
                'binary',
                'email',
                'C:34:"Tienvx\Bundle\MbtBundle\Graph\Path":423:{a:3:{i:0;a:5:{i:0;N;i:1;s:23:"viewAnyCategoryFromHome";i:2;s:17:"viewOtherCategory";i:3;s:15:"addFromCategory";i:4;s:20:"checkoutFromCategory";}i:1;a:5:{i:0;N;i:1;a:1:{s:8:"category";s:2:"34";}i:2;a:1:{s:8:"category";s:2:"57";}i:3;a:1:{s:7:"product";s:2:"49";}i:4;a:0:{}}i:2;a:5:{i:0;a:1:{i:0;s:4:"home";}i:1;a:1:{i:0;s:8:"category";}i:2;a:1:{i:0;s:8:"category";}i:3;a:1:{i:0;s:8:"category";}i:4;a:1:{i:0;s:8:"checkout";}}}}',
                5
            ],
            /*[
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
            ],*/
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
