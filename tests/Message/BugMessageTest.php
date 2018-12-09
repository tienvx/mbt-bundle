<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class BugMessageTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param array $pathArgs
     * @param string $reducer
     * @param array $expectedPathArgs
     * @dataProvider consumeMessageData
     * @throws \Exception
     */
    public function testExecute(string $model, array $pathArgs, string $reducer, array $expectedPathArgs)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        $path = new Path(...$pathArgs);
        $expectedPath = new Path(...$expectedPathArgs);
        $bugMessage = ($model === 'shopping_cart') ? 'You added an out-of-stock product into cart! Can not checkout' :
            'Should login automatically after registering';

        $task = new Task();
        $task->setTitle('Test task title');
        $task->setModel($model);
        $task->setGenerator('random');
        $task->setReducer($reducer);
        $entityManager->persist($task);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearLog();

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setPath(serialize($path));
        $bug->setLength($path->countPlaces());
        $bug->setTask($task);
        $bug->setBugMessage($bugMessage);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->consumeMessages();

        $entityManager->refresh($bug);

        /** @var Bug[] $bugs */
        $bugs = $entityManager->getRepository(Bug::class)->findBy(['task' => $task->getId()]);

        $this->assertEquals(1, count($bugs));
        $this->assertEquals($bugMessage, $bugs[0]->getBugMessage());
        if ($reducer !== 'random') {
            $this->assertEquals(serialize($expectedPath), $bugs[0]->getPath());
            $this->assertEquals($expectedPath->countPlaces(), $bugs[0]->getLength());
        } else {
            $this->assertLessThanOrEqual($expectedPath->countPlaces(), $bugs[0]->getLength());
        }

        $this->assertTrue($this->hasLog());
        $this->assertEquals('reported', $bug->getStatus());
    }

    public function consumeMessageData()
    {
        return [
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => 57], ['product' => 49], []],
                    [['home'], ['category'], ['category'], ['checkout']]
                ],
                'binary',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => 57], ['product' => 49], []],
                    [['home'], ['category'], ['category'], ['checkout']]
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'addFromProduct', 'checkoutFromProduct', 'viewCartFromCheckout', 'viewProductFromCart', 'viewAnyCategoryFromProduct', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '34'], ['product' => '48'], [], [], [], ['product' => '48'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['product'], ['product'], ['checkout'], ['cart'], ['product'], ['category'], ['category'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '34'], ['product' => '48'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'addFromHome', 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['product' => '40'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['home'], ['category'], ['category'], ['checkout']],
                ],
                'binary',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewCartFromCategory', 'backToHomeFromCart', 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'addFromProduct', 'checkoutFromProduct'],
                    [null, ['category' => '33'], ['product' => '31'], [], [], ['category' => '57'], ['product' => '49'], [], []],
                    [['home'], ['category'], ['category'], ['cart'], ['home'], ['category'], ['product'], ['product'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'addFromProduct', 'checkoutFromProduct'],
                    [null, ['category' => '57'], ['product' => '49'], [], []],
                    [['home'], ['category'], ['product'], ['product'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewOtherCategory', 'viewProductFromCategory', 'backToHomeFromProduct', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], ['category' => '34'], ['product' => '48'], [], []],
                    [['home'], ['category'], ['category'], ['category'], ['product'], ['home'], ['checkout']],
                ],
                'binary',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewCartFromHome', 'backToHomeFromCart', 'viewAnyCategoryFromHome', 'addFromCategory', 'viewOtherCategory', 'viewOtherCategory', 'checkoutFromCategory'],
                    [null, [], [], ['category' => '57'], ['product' => '49'], ['category' => '25_28'], ['category' => '20'], []],
                    [['home'], ['cart'], ['home'], ['category'], ['category'], ['category'], ['category'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'checkoutFromHome', 'backToHomeFromCheckout', 'viewAnyCategoryFromHome', 'addFromCategory', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'addFromCategory', 'viewCartFromCategory', 'viewProductFromCart', 'viewAnyCategoryFromProduct', 'checkoutFromCategory'],
                    [null, [], [], ['category' => '20'], ['product' => '46'], ['product' => '33'], ['category' => '57'], ['product' => '49'], [], ['product' => '46'], ['category' => '57'], []],
                    [['home'], ['checkout'], ['home'], ['category'], ['category'], ['product'], ['category'], ['category'], ['cart'], ['product'], ['category'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '20'], ['product' => '33'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'viewOtherCategory', 'viewOtherCategory', 'viewProductFromCategory', 'addFromProduct', 'viewAnyCategoryFromProduct', 'addFromCategory', 'viewOtherCategory', 'viewOtherCategory', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '20_27'], ['product' => '41'], ['category' => '24'], ['category' => '17'], ['category' => '24'], ['product' => '28'], [], ['category' => '57'], ['product' => '49'], ['category' => '20_27'], ['category' => '20'], ['product' => '33'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['category'], ['product'], ['product'], ['category'], ['category'], ['category'], ['category'], ['category'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '20_27'], ['product' => '41'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewOtherCategory', 'viewProductFromCategory', 'backToHomeFromProduct', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], ['category' => '34'], ['product' => '48'], [], []],
                    [['home'], ['category'], ['category'], ['category'], ['product'], ['home'], ['checkout']],
                ],
                'binary',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewProductFromCategory', 'backToHomeFromProduct', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], ['product' => '48'], [], []],
                    [['home'], ['category'], ['category'], ['product'], ['home'], ['checkout']],
                ],
                'binary',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
            [
                'checkout',
                [
                    [null, 'addProductAndCheckoutNotLoggedIn', 'registerAccount', 'addBillingAddress', 'addDeliveryAddress', 'addDeliveryMethod', 'addPaymentMethod', 'confirmOrder', 'continueShopping', 'addProductAndCheckoutNotLoggedIn', 'guestCheckout', 'addBillingAddress', 'addDeliveryAddress', 'addDeliveryMethod', 'addPaymentMethod', 'confirmOrder', 'continueShopping', 'addProductAndCheckoutNotLoggedIn', 'login'],
                    [null, [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []],
                    [['home'], ['awaitingAccount'], ['accountAdded', 'awaitingBillingAddress', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'paymentMethodAdded'], ['orderPlaced'], ['home'], ['awaitingAccount'], ['accountAdded', 'awaitingBillingAddress', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'paymentMethodAdded'], ['orderPlaced'], ['home'], ['awaitingAccount'], ['accountAdded', 'awaitingBillingAddress', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod']]
                ],
                'loop',
                [
                    [null, 'addProductAndCheckoutNotLoggedIn', 'registerAccount', 'addBillingAddress', 'addDeliveryAddress', 'addDeliveryMethod', 'addPaymentMethod', 'confirmOrder', 'continueShopping', 'addProductAndCheckoutNotLoggedIn', 'login'],
                    [null, [], [], [], [], [], [], [], [], [], []],
                    [['home'], ['awaitingAccount'], ['accountAdded', 'awaitingBillingAddress', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'awaitingDeliveryMethod', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'paymentMethodAdded'], ['orderPlaced'], ['home'], ['awaitingAccount'], ['accountAdded', 'awaitingBillingAddress', 'awaitingDeliveryAddress', 'awaitingDeliveryMethod', 'awaitingPaymentMethod']]
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'addFromHome', 'checkoutFromHome', 'backToHomeFromCheckout', 'addFromHome', 'addFromHome', 'addFromHome', 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['product' => '40'], [], [], ['product' => '42'], ['product' => '30'], ['product' => '43'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['home'], ['checkout'], ['home'], ['home'], ['home'], ['home'], ['category'], ['category'], ['checkout']],
                ],
                'random',
                [
                    [null, 'addFromHome', 'checkoutFromHome', 'backToHomeFromCheckout', 'addFromHome', 'addFromHome', 'addFromHome', 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['product' => '40'], [], [], ['product' => '42'], ['product' => '30'], ['product' => '43'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['home'], ['checkout'], ['home'], ['home'], ['home'], ['home'], ['category'], ['category'], ['checkout']],
                ]
            ],
        ];
    }
}
