<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Reporter;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class BugMessageTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param array  $pathArgs
     * @param string $reducer
     * @param array  $expectedPathArgs
     * @dataProvider consumeMessageData
     *
     * @throws \Exception
     */
    public function testExecute(string $model, array $pathArgs, string $reducer, array $expectedPathArgs)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        $path = new Path(...$pathArgs);
        $expectedPath = new Path(...$expectedPathArgs);
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
        $task->setReporters([new Reporter('in-memory')]);
        // Does not matter, because we are testing reducer.
        //$task->setGeneratorOptions($generatorOptions);
        $entityManager->persist($task);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearReport();
        // Just to make sure
        $this->removeScreenshots();

        $bug = new Bug();
        $bug->setTitle('Test bug title');
        $bug->setPath($path);
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
        if ('random' !== $reducer) {
            $this->assertEquals(Path::serialize($expectedPath), Path::serialize($bugs[0]->getPath()));
            $this->assertEquals($expectedPath->countPlaces(), $bugs[0]->getLength());
        } else {
            $this->assertLessThanOrEqual($expectedPath->countPlaces(), $bugs[0]->getLength());
        }

        $this->assertTrue($this->hasReport($bugs[0]));
        $this->assertEquals('reported', $bug->getStatus());

        // Because screenshots had not been captured, and had been removed during set-up, no need to test this
        //$this->assertTrue($this->reportHasScreenshot());
    }

    public function consumeMessageData()
    {
        return [
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => 57], ['product' => 49], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ],
                'split',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => 57], ['product' => 49], []],
                    [['home'], ['category'], ['category'], ['checkout']],
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
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'addFromHome', 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['product' => '40'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['home'], ['category'], ['category'], ['checkout']],
                ],
                'split',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ],
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
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewOtherCategory', 'viewProductFromCategory', 'backToHomeFromProduct', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], ['category' => '34'], ['product' => '48'], [], []],
                    [['home'], ['category'], ['category'], ['category'], ['product'], ['home'], ['checkout']],
                ],
                'split',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ],
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
                ],
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
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'viewOtherCategory', 'viewOtherCategory', 'viewProductFromCategory', 'addFromProduct', 'viewAnyCategoryFromProduct', 'addFromCategory', 'viewOtherCategory', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '20_27'], ['product' => '41'], ['category' => '24'], ['category' => '17'], ['category' => '24'], ['product' => '28'], [], ['category' => '57'], ['product' => '49'], ['category' => '20'], ['product' => '33'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['category'], ['product'], ['product'], ['category'], ['category'], ['category'], ['category'], ['checkout']],
                ],
                'loop',
                [
                    [null, 'viewAnyCategoryFromHome', 'viewProductFromCategory', 'viewAnyCategoryFromProduct', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '20_27'], ['product' => '41'], ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['product'], ['category'], ['category'], ['checkout']],
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewOtherCategory', 'viewProductFromCategory', 'backToHomeFromProduct', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], ['category' => '34'], ['product' => '48'], [], []],
                    [['home'], ['category'], ['category'], ['category'], ['product'], ['home'], ['checkout']],
                ],
                'split',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ],
            ],
            [
                'shopping_cart',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'viewCartFromCategory', 'backToHomeFromCart', 'checkoutFromHome'],
                    [null, ['category' => '57'], ['product' => '49'], [], [], []],
                    [['home'], ['category'], ['category'], ['cart'], ['home'], ['checkout']],
                ],
                'split',
                [
                    [null, 'viewAnyCategoryFromHome', 'addFromCategory', 'checkoutFromCategory'],
                    [null, ['category' => '57'], ['product' => '49'], []],
                    [['home'], ['category'], ['category'], ['checkout']],
                ],
            ],
            [
                'checkout',
                [
                    [null, 'addProductAndCheckoutNotLoggedIn', 'guestCheckout', 'fillPersonalDetails', 'fillBillingAddress', 'guestCheckoutAndAddBillingAddress', 'useExistingDeliveryAddress', 'addDeliveryMethod', 'addPaymentMethod', 'confirmOrder', 'continueShopping', 'addProductAndCheckoutNotLoggedIn', 'registerAccount', 'fillPersonalDetails', 'fillPassword', 'fillBillingAddress', 'registerAndAddBillingAddress'],
                    [null, [], [], [], [], [], [], [], [], [], [], [], [], [], [], [], []],
                    [['home'], ['awaitingAccount'], ['awaitingPersonalDetails', 'awaitingBillingAddress'], ['personalDetailsFilled', 'awaitingBillingAddress'], ['personalDetailsFilled', 'billingAddressFilled'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'awaitingDeliveryMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'awaitingPaymentMethod'], ['accountAdded', 'billingAddressAdded', 'deliveryAddressAdded', 'deliveryMethodAdded', 'paymentMethodAdded', 'awaitingOrderConfirm'], ['orderPlaced'], ['home'], ['awaitingAccount'], ['awaitingPersonalDetails', 'awaitingPassword', 'awaitingBillingAddress'], ['personalDetailsFilled', 'awaitingPassword', 'awaitingBillingAddress'], ['personalDetailsFilled', 'passwordFilled', 'awaitingBillingAddress'], ['personalDetailsFilled', 'passwordFilled', 'billingAddressFilled'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress']],
                ],
                'loop',
                [
                    [null, 'addProductAndCheckoutNotLoggedIn', 'registerAccount', 'fillPersonalDetails', 'fillPassword', 'fillBillingAddress', 'registerAndAddBillingAddress'],
                    [null, [], [], [], [], [], []],
                    [['home'], ['awaitingAccount'], ['awaitingPersonalDetails', 'awaitingPassword', 'awaitingBillingAddress'], ['personalDetailsFilled', 'awaitingPassword', 'awaitingBillingAddress'], ['personalDetailsFilled', 'passwordFilled', 'awaitingBillingAddress'], ['personalDetailsFilled', 'passwordFilled', 'billingAddressFilled'], ['accountAdded', 'billingAddressAdded', 'awaitingDeliveryAddress']],
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
                ],
            ],
            [
                'product',
                [
                    [null, 'selectOptions', 'selectSelect', 'selectTime', 'selectDateTime', 'fillText', 'selectFile'],
                    [null, [], ['select' => 2], [], [], [], []],
                    [['product'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingText', 'awaitingSelect', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'awaitingTime', 'awaitingDateTime'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingText', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'awaitingTime', 'awaitingDateTime', 'selectSelected'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingText', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'awaitingDateTime', 'selectSelected', 'timeSelected'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingText', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'selectSelected', 'timeSelected', 'dateTimeSelected'], 	['awaitingRadio', 'awaitingCheckbox', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'selectSelected', 'timeSelected', 'dateTimeSelected', 'textFilled'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'selectSelected', 'timeSelected', 'dateTimeSelected', 'textFilled']],
                ],
                'transition',
                [
                    [null, 'selectOptions', 'selectFile'],
                    [null, [], []],
                    [['product'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingText', 'awaitingSelect', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'awaitingTime', 'awaitingDateTime'], ['awaitingRadio', 'awaitingCheckbox', 'awaitingTextarea', 'awaitingFile', 'awaitingDate', 'awaitingSelect', 'awaitingTime', 'awaitingDateTime', 'awaitingText']],
                ],
            ],
        ];
    }
}
