<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\TraceableMessageBus;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;

class ReproducePathMessageTest extends MessageTestCase
{
    /**
     * @param string $steps
     * @param int $length
     * @param string $expectedSteps
     * @param int $expectedLength
     * @throws \Exception
     * @dataProvider consumeMessageData
     */
    public function testConsumeMessage(string $steps, int $length, string $reducer, string $expectedSteps, int $expectedLength)
    {
        /** @var TraceableMessageBus $messageBus */
        $messageBus = self::$container->get(MessageBusInterface::class);
        /** @var ContainerInterface $receiverLocator */
        $receiverLocator = self::$container->get('messenger.receiver_locator');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $this->application->add(new ConsumeMessagesCommand($messageBus, $receiverLocator));

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel('shopping_cart');
        $task->setGenerator('random');
        $task->setStopCondition('max-length');
        $task->setStopConditionArguments('{}');
        $task->setReducer('queued-loop');
        $task->setProgress(0);
        $task->setStatus('not-started');
        $entityManager->persist($task);

        $reproducePath = new ReproducePath();
        $reproducePath->setSteps($steps);
        $reproducePath->setLength($length);
        $reproducePath->setTask($task);
        $reproducePath->setBugMessage('You added an out-of-stock product into cart! Can not checkout');
        $entityManager->persist($reproducePath);

        $entityManager->flush();

        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findBy(['reproducePath' => $reproducePath->getId()]);

        if (count($bugs)) {
            $this->assertEquals(1, count($bugs));
            $this->assertEquals('You added an out-of-stock product into cart! Can not checkout', $bugs[0]->getReproducePath()->getBugMessage());
            $this->assertEquals($expectedSteps, $bugs[0]->getReproducePath()->getSteps());
            $this->assertEquals($expectedLength, $bugs[0]->getReproducePath()->getLength());
        } else {
            $this->assertEquals(0, count($bugs));
        }
    }

    public function consumeMessageData()
    {
        return [
            [
                'home viewProductFromHome(product=49) product addFromProduct() product viewCartFromProduct() cart',
                3,
                'queued-loop',
                '',
                0
            ],
            [
                'home viewAnyCategoryFromHome(category=24) category addFromCategory(product=29) category viewProductFromCategory(product=40) product checkoutFromProduct() checkout',
                4,
                'queued-loop',
                '',
                0
            ],
            [
                'home viewAnyCategoryFromHome(category=34) category viewProductFromCategory(product=48) product addFromProduct() product checkoutFromProduct() checkout viewCartFromCheckout() cart viewProductFromCart(product=48) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                9,
                'queued-loop',
                'home viewAnyCategoryFromHome(category=34) category viewProductFromCategory(product=48) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
            [
                'home addFromHome(product=40) home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                4,
                'greedy',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home viewAnyCategoryFromHome(category=33) category addFromCategory(product=31) category viewCartFromCategory() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category viewProductFromCategory(product=49) product addFromProduct() product checkoutFromProduct() checkout',
                8,
                'binary',
                'home viewAnyCategoryFromHome(category=57) category viewProductFromCategory(product=49) product addFromProduct() product checkoutFromProduct() checkout',
                4
            ],
            [
                'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category viewOtherCategory(category=34) category viewProductFromCategory(product=48) product backToHomeFromProduct() home checkoutFromHome() checkout',
                7,
                'binary',
                'home viewAnyCategoryFromHome(category=34) category viewOtherCategory(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                4
            ],
            [
                'home viewCartFromHome() cart backToHomeFromCart() home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category viewOtherCategory(category=25_28) category viewOtherCategory(category=20) category checkoutFromCategory() checkout',
                7,
                'greedy',
                'home viewAnyCategoryFromHome(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                3
            ],
            [
                'home checkoutFromHome() checkout backToHomeFromCheckout() home viewAnyCategoryFromHome(category=20) category addFromCategory(product=46) category viewProductFromCategory(product=33) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category viewCartFromCategory() cart viewProductFromCart(product=46) product viewAnyCategoryFromProduct(category=57) category checkoutFromCategory() checkout',
                11,
                'loop',
                'home viewAnyCategoryFromHome(category=20) category viewProductFromCategory(product=33) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
            [
                'home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=24) category viewOtherCategory(category=17) category viewOtherCategory(category=24) category viewProductFromCategory(product=28) product addFromProduct() product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category viewOtherCategory(category=20_27) category viewOtherCategory(category=20) category addFromCategory(product=33) category checkoutFromCategory() checkout',
                13,
                'queued-loop',
                'home viewAnyCategoryFromHome(category=20_27) category viewProductFromCategory(product=41) product viewAnyCategoryFromProduct(category=57) category addFromCategory(product=49) category checkoutFromCategory() checkout',
                5
            ],
        ];
    }
}
