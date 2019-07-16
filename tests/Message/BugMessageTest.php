<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Doctrine\ORM\EntityManagerInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Reporter;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Path;

class BugMessageTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param array  $steps
     * @param string $reducer
     * @param array  $expectedSteps
     * @dataProvider consumeMessageData
     *
     * @throws \Exception
     */
    public function testExecute(string $model, array $steps, string $reducer, array $expectedSteps)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);
        $path = Path::denormalize($steps);
        $expectedPath = Path::denormalize($expectedSteps);
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
            $this->assertEquals($expectedPath->serialize(), $bugs[0]->getPath()->serialize());
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
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => ['home'],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [['key' => 'category', 'value' => 57]],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [['key' => 'product', 'value' => 49]],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [],
                        'places' => ['checkout'],
                    ],
                ],
                'split',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => ['home'],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [['key' => 'category', 'value' => 57]],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [['key' => 'product', 'value' => 49]],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [],
                        'places' => ['checkout'],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => ['home'],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [['key' => 'category', 'value' => '34']],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [['key' => 'product', 'value' => '48']],
                        'places' => ['product'],
                    ],
                    [
                        'transition' => 'addFromProduct',
                        'data' => [],
                        'places' => ['product'],
                    ],
                    [
                        'transition' => 'checkoutFromProduct',
                        'data' => [],
                        'places' => ['checkout'],
                    ],
                    [
                        'transition' => 'viewCartFromCheckout',
                        'data' => [],
                        'places' => ['cart'],
                    ],
                    [
                        'transition' => 'viewProductFromCart',
                        'data' => [['key' => 'product', 'value' => '48']],
                        'places' => ['product'],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [['key' => 'category', 'value' => '57']],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [['key' => 'product', 'value' => '49']],
                        'places' => ['category'],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [],
                        'places' => ['checkout'],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '34',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '48',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '40',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'split',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '33',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '31',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewCartFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'cart',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCart',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'addFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'addFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '34',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '48',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'split',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewCartFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'cart',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCart',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '25_28',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '20',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCheckout',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '20',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '46',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '33',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewCartFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'cart',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCart',
                        'data' => [[
                            'key' => 'product', 'value' => '46',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '20',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '33',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '20_27',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '41',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '24',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '17',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '24',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '28',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'addFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '20',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '33',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '20_27',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '41',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromProduct',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewOtherCategory',
                        'data' => [[
                            'key' => 'category', 'value' => '34',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewProductFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '48',
                        ]],
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromProduct',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'split',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'viewCartFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'cart',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCart',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'split',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'checkout',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addProductAndCheckoutNotLoggedIn',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingAccount',
                        ],
                    ],
                    [
                        'transition' => 'guestCheckout',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingPersonalDetails',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillPersonalDetails',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'billingAddressFilled',
                        ],
                    ],
                    [
                        'transition' => 'guestCheckoutAndAddBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'awaitingDeliveryAddress',
                        ],
                    ],
                    [
                        'transition' => 'useExistingDeliveryAddress',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'deliveryAddressAdded',
                            'awaitingDeliveryMethod',
                        ],
                    ],
                    [
                        'transition' => 'addDeliveryMethod',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'deliveryAddressAdded',
                            'deliveryMethodAdded',
                            'awaitingPaymentMethod',
                        ],
                    ],
                    [
                        'transition' => 'addPaymentMethod',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'deliveryAddressAdded',
                            'deliveryMethodAdded',
                            'paymentMethodAdded',
                            'awaitingOrderConfirm',
                        ],
                    ],
                    [
                        'transition' => 'confirmOrder',
                        'data' => [
                        ],
                        'places' => [
                            'orderPlaced',
                        ],
                    ],
                    [
                        'transition' => 'continueShopping',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addProductAndCheckoutNotLoggedIn',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingAccount',
                        ],
                    ],
                    [
                        'transition' => 'registerAccount',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingPersonalDetails',
                            'awaitingPassword',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillPersonalDetails',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'awaitingPassword',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillPassword',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'passwordFilled',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'passwordFilled',
                            'billingAddressFilled',
                        ],
                    ],
                    [
                        'transition' => 'registerAndAddBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'awaitingDeliveryAddress',
                        ],
                    ],
                ],
                'loop',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addProductAndCheckoutNotLoggedIn',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingAccount',
                        ],
                    ],
                    [
                        'transition' => 'registerAccount',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingPersonalDetails',
                            'awaitingPassword',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillPersonalDetails',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'awaitingPassword',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillPassword',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'passwordFilled',
                            'awaitingBillingAddress',
                        ],
                    ],
                    [
                        'transition' => 'fillBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'personalDetailsFilled',
                            'passwordFilled',
                            'billingAddressFilled',
                        ],
                    ],
                    [
                        'transition' => 'registerAndAddBillingAddress',
                        'data' => [
                        ],
                        'places' => [
                            'accountAdded',
                            'billingAddressAdded',
                            'awaitingDeliveryAddress',
                        ],
                    ],
                ],
            ],
            [
                'shopping_cart',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '40',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCheckout',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '42',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '30',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '43',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
                'random',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '40',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromHome',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                    [
                        'transition' => 'backToHomeFromCheckout',
                        'data' => [
                        ],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '42',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '30',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'addFromHome',
                        'data' => [[
                            'key' => 'product', 'value' => '43',
                        ]],
                        'places' => [
                            'home',
                        ],
                    ],
                    [
                        'transition' => 'viewAnyCategoryFromHome',
                        'data' => [[
                            'key' => 'category', 'value' => '57',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'addFromCategory',
                        'data' => [[
                            'key' => 'product', 'value' => '49',
                        ]],
                        'places' => [
                            'category',
                        ],
                    ],
                    [
                        'transition' => 'checkoutFromCategory',
                        'data' => [
                        ],
                        'places' => [
                            'checkout',
                        ],
                    ],
                ],
            ],
            [
                'product',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'selectOptions',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingText',
                            'awaitingSelect',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'awaitingTime',
                            'awaitingDateTime',
                        ],
                    ],
                    [
                        'transition' => 'selectSelect',
                        'data' => [[
                            'key' => 'select', 'value' => 2,
                        ]],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingText',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'awaitingTime',
                            'awaitingDateTime',
                            'selectSelected',
                        ],
                    ],
                    [
                        'transition' => 'selectTime',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingText',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'awaitingDateTime',
                            'selectSelected',
                            'timeSelected',
                        ],
                    ],
                    [
                        'transition' => 'selectDateTime',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingText',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'selectSelected',
                            'timeSelected',
                            'dateTimeSelected',
                        ],
                    ],
                    [
                        'transition' => 'fillText',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'selectSelected',
                            'timeSelected',
                            'dateTimeSelected',
                            'textFilled',
                        ],
                    ],
                    [
                        'transition' => 'selectFile',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'selectSelected',
                            'timeSelected',
                            'dateTimeSelected',
                            'textFilled',
                        ],
                    ],
                ],
                'transition',
                [
                    [
                        'transition' => null,
                        'data' => null,
                        'places' => [
                            'product',
                        ],
                    ],
                    [
                        'transition' => 'selectOptions',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingText',
                            'awaitingSelect',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'awaitingTime',
                            'awaitingDateTime',
                        ],
                    ],
                    [
                        'transition' => 'selectFile',
                        'data' => [
                        ],
                        'places' => [
                            'awaitingRadio',
                            'awaitingCheckbox',
                            'awaitingTextarea',
                            'awaitingFile',
                            'awaitingDate',
                            'awaitingSelect',
                            'awaitingTime',
                            'awaitingDateTime',
                            'awaitingText',
                        ],
                    ],
                ],
            ],
        ];
    }
}
