<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Step\Builder;

use Generator;
use Petrinet\Model\PlaceInterface;
use Petrinet\Model\TransitionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\ShortestPathStepsBuilder;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Step\Builder\ShortestPathStepsBuilder
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic
 * @uses \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @uses \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 */
class ShortestPathStepsBuilderTest extends TestCase
{
    protected ColorfulFactoryInterface $factory;
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected PetrinetInterface $petrinet;
    protected PetrinetDomainLogic $petrinetDomainLogic;
    protected ShortestPathStepsBuilder $stepsBuilder;
    protected PetrinetHelperInterface|MockObject $petrinetHelper;
    protected Revision $revision;
    protected Bug $bug;
    protected PlaceInterface $cartEmpty;
    protected PlaceInterface $cartHasProducts;
    protected PlaceInterface $checkout;
    protected PlaceInterface $address;
    protected PlaceInterface $shipping;
    protected PlaceInterface $payment;
    protected PlaceInterface $order;
    protected TransitionInterface $clearCart;
    protected TransitionInterface $removeLastProduct;
    protected TransitionInterface $removeProduct;
    protected TransitionInterface $addFirstProduct;
    protected TransitionInterface $addMoreProduct;
    protected TransitionInterface $updateCart;
    protected TransitionInterface $goToCheckout;
    protected TransitionInterface $fillAddress;
    protected TransitionInterface $chooseShipping;
    protected TransitionInterface $choosePayment;
    protected TransitionInterface $confirmOrder;

    protected function setUp(): void
    {
        $this->factory = new ColorfulFactory();
        $this->transitionService = new GuardedTransitionService($this->factory);
        $this->markingHelper = new MarkingHelper($this->factory);
        $builder = new SingleColorPetrinetBuilder($this->factory);
        $this->initPlaces($builder);
        $this->initTransitions($builder);
        $this->initPetrinet($builder);
        $this->initPetrinetDomainLogic();
        $this->initBug();
        $this->initStepsBuilder();
    }

    protected function initPlaces(SingleColorPetrinetBuilder $builder): void
    {
        $this->cartHasProducts = $builder->place();
        $this->cartEmpty = $builder->place();
        $this->checkout = $builder->place();
        $this->address = $builder->place();
        $this->shipping = $builder->place();
        $this->payment = $builder->place();
        $this->order = $builder->place();
        $this->cartHasProducts->setId(0);
        $this->cartEmpty->setId(1);
        $this->checkout->setId(2);
        $this->address->setId(3);
        $this->shipping->setId(4);
        $this->payment->setId(5);
        $this->order->setId(6);
    }

    protected function initTransitions(SingleColorPetrinetBuilder $builder): void
    {
        $emptyProducts = fn (ColorInterface $color): array => ['products' => 0];
        $oneProduct = fn (ColorInterface $color): array => ['products' => 1];
        $hasOneProduct = fn (ColorInterface $color): bool => 1 === $color->getValue('products');
        $hasMoreThanOneProduct = fn (ColorInterface $color): bool => $color->getValue('products') > 1;
        $removeProduct = fn (ColorInterface $color): array => ['products' => $color->getValue('products') - 1];
        $addProduct = fn (ColorInterface $color): array => ['products' => $color->getValue('products') + 1];

        $this->clearCart = $builder->transition(null, $emptyProducts);
        $this->removeLastProduct = $builder->transition($hasOneProduct, $emptyProducts);
        $this->removeProduct = $builder->transition($hasMoreThanOneProduct, $removeProduct);
        $this->addFirstProduct = $builder->transition(null, $oneProduct);
        $this->addMoreProduct = $builder->transition(null, $addProduct);
        $this->updateCart = $builder->transition();
        $this->goToCheckout = $builder->transition();
        $this->fillAddress = $builder->transition();
        $this->chooseShipping = $builder->transition();
        $this->choosePayment = $builder->transition();
        $this->confirmOrder = $builder->transition();
        $this->clearCart->setId(0);
        $this->removeLastProduct->setId(1);
        $this->removeProduct->setId(2);
        $this->addFirstProduct->setId(3);
        $this->addMoreProduct->setId(4);
        $this->updateCart->setId(5);
        $this->goToCheckout->setId(6);
        $this->fillAddress->setId(7);
        $this->chooseShipping->setId(8);
        $this->choosePayment->setId(9);
        $this->confirmOrder->setId(10);
    }

    protected function initPetrinet(SingleColorPetrinetBuilder $builder): void
    {
        $this->petrinet = $builder
            ->connect($this->cartHasProducts, $this->clearCart)
            ->connect($this->clearCart, $this->cartEmpty, 1)
            ->connect($this->cartHasProducts, $this->removeLastProduct)
            ->connect($this->removeLastProduct, $this->cartEmpty, 1)
            ->connect($this->cartHasProducts, $this->removeProduct)
            ->connect($this->removeProduct, $this->cartHasProducts, 1)
            ->connect($this->cartEmpty, $this->addFirstProduct)
            ->connect($this->addFirstProduct, $this->cartHasProducts, 1)
            ->connect($this->cartHasProducts, $this->addMoreProduct)
            ->connect($this->addMoreProduct, $this->cartHasProducts, 1)
            ->connect($this->cartHasProducts, $this->updateCart)
            ->connect($this->updateCart, $this->cartHasProducts)
            ->connect($this->cartHasProducts, $this->goToCheckout)
            ->connect($this->goToCheckout, $this->checkout)
            ->connect($this->checkout, $this->fillAddress)
            ->connect($this->fillAddress, $this->address)
            ->connect($this->address, $this->chooseShipping)
            ->connect($this->chooseShipping, $this->shipping)
            ->connect($this->shipping, $this->choosePayment)
            ->connect($this->choosePayment, $this->payment)
            ->connect($this->payment, $this->confirmOrder)
            ->connect($this->confirmOrder, $this->order)
            ->getPetrinet();
    }

    protected function initPetrinetDomainLogic(): void
    {
        $this->petrinetDomainLogic = new PetrinetDomainLogic($this->transitionService, $this->markingHelper);
        $this->petrinetDomainLogic->setPetrinet($this->petrinet);
    }

    protected function initBug(): void
    {
        $this->bug = new Bug();
        $this->bug->setSteps([
            new Step([$this->cartEmpty->getId() => 1], $this->geColor(0), $this->clearCart->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(1), $this->addFirstProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(2), $this->addMoreProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(3), $this->addMoreProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(4), $this->addMoreProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(3), $this->removeProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(4), $this->addMoreProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(3), $this->removeProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(2), $this->removeProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(1), $this->removeProduct->getId()),
            new Step([$this->cartEmpty->getId() => 1], $this->geColor(0), $this->removeLastProduct->getId()),
            new Step([$this->cartHasProducts->getId() => 1], $this->geColor(1), $this->addFirstProduct->getId()),
            new Step([$this->checkout->getId() => 1], $this->geColor(1), $this->goToCheckout->getId()),
            new Step([$this->address->getId() => 1], $this->geColor(1), $this->fillAddress->getId()),
            new Step([$this->shipping->getId() => 1], $this->geColor(1), $this->chooseShipping->getId()),
            new Step([$this->payment->getId() => 1], $this->geColor(1), $this->choosePayment->getId()),
            new Step([$this->order->getId() => 1], $this->geColor(1), $this->confirmOrder->getId()),
        ]);
        $this->revision = new Revision();
        $task = new Task();
        $task->setModelRevision($this->revision);
        $this->bug->setTask($task);
    }

    protected function geColor(int $products): Color
    {
        return new Color(['products' => $products]);
    }

    protected function initStepsBuilder(): void
    {
        $this->petrinetHelper = $this->createMock(PetrinetHelperInterface::class);
        $this->stepsBuilder = new ShortestPathStepsBuilder($this->petrinetHelper, $this->petrinetDomainLogic);
    }

    protected function expectsPetrinetHelper(): void
    {
        $this->petrinetHelper
            ->expects($this->once())
            ->method('build')
            ->with($this->revision)
            ->willReturn($this->petrinet);
    }

    /**
     * @dataProvider invalidRangeProvider
     */
    public function testGetInvalidRange(int $from, int $to): void
    {
        $this->expectExceptionObject(new OutOfRangeException('Can not create new steps using invalid range'));
        iterator_to_array($this->stepsBuilder->create($this->bug, $from, $to));
    }

    public function invalidRangeProvider(): array
    {
        $validMinFrom = 0;
        $validMaxTo = 16;

        return [
            [-1, $validMaxTo],
            [$validMinFrom, 17],
            [-1, 17],
        ];
    }

    public function testGetShortestPathFromCartEmptyToCheckout(): void
    {
        $this->expectsPetrinetHelper();
        $nodes = $this->stepsBuilder->create($this->bug, 0, 12);
        $this->assertNodes([
            [
                'transition' => $this->clearCart->getId(),
                'places' => [$this->cartEmpty->getId() => 1],
                'color' => ['products' => 0],
            ],
            [
                'transition' => $this->addFirstProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->goToCheckout->getId(),
                'places' => [$this->checkout->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->fillAddress->getId(),
                'places' => [$this->address->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->chooseShipping->getId(),
                'places' => [$this->shipping->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->choosePayment->getId(),
                'places' => [$this->payment->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->confirmOrder->getId(),
                'places' => [$this->order->getId() => 1],
                'color' => ['products' => 1],
            ],
        ], $nodes);
    }

    public function testGetShortestPathFromCartHasProductsToShipping(): void
    {
        $this->expectsPetrinetHelper();
        $nodes = $this->stepsBuilder->create($this->bug, 4, 14);
        $this->assertNodes([
            [
                'transition' => $this->clearCart->getId(),
                'places' => [$this->cartEmpty->getId() => 1],
                'color' => ['products' => 0],
            ],
            [
                'transition' => $this->addFirstProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->addMoreProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 2],
            ],
            [
                'transition' => $this->addMoreProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 3],
            ],
            [
                'transition' => $this->addMoreProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 4],
            ],
            [
                'transition' => $this->clearCart->getId(),
                'places' => [$this->cartEmpty->getId() => 1],
                'color' => ['products' => 0],
            ],
            [
                'transition' => $this->addFirstProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->goToCheckout->getId(),
                'places' => [$this->checkout->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->fillAddress->getId(),
                'places' => [$this->address->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->chooseShipping->getId(),
                'places' => [$this->shipping->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->choosePayment->getId(),
                'places' => [$this->payment->getId() => 1],
                'color' => ['products' => 1],
            ],
            [
                'transition' => $this->confirmOrder->getId(),
                'places' => [$this->order->getId() => 1],
                'color' => ['products' => 1],
            ],
        ], $nodes);
    }

    protected function assertNodes(array $expectedNodes, Generator $nodes): void
    {
        foreach (iterator_to_array($nodes, false) as $index => $node) {
            $this->assertInstanceOf(Step::class, $node);
            $this->assertSame($expectedNodes[$index]['transition'], $node->getTransition());
            $this->assertSame($expectedNodes[$index]['places'], $node->getPlaces());
            $this->assertSame($expectedNodes[$index]['color'], $node->getColor()->getValues());
        }
    }
}
