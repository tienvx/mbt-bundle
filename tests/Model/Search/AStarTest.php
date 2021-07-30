<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Search;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\PlaceInterface;
use Petrinet\Model\TransitionInterface;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Model\ColorInterface;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic;
use Tienvx\Bundle\MbtBundle\Service\AStar\Node;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\AStar\PetrinetDomainLogic
 * @covers \Tienvx\Bundle\MbtBundle\Service\AStar\Node
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 */
class AStarTest extends TestCase
{
    protected ColorfulFactoryInterface $factory;
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected PetrinetInterface $petrinet;
    protected PlaceInterface $cartEmpty;
    protected PlaceInterface $cartHasProducts;
    protected PlaceInterface $checkout;
    protected TransitionInterface $clearCart;
    protected TransitionInterface $goToCheckout;
    protected TransitionInterface $removeLastProduct;
    protected TransitionInterface $removeProduct;
    protected TransitionInterface $addFirstProduct;
    protected TransitionInterface $addMoreProduct;
    protected TransitionInterface $updateCart;
    protected PetrinetDomainLogic $aStar;

    protected function setUp(): void
    {
        $this->factory = new ColorfulFactory();
        $this->transitionService = new GuardedTransitionService($this->factory);
        $this->markingHelper = new MarkingHelper($this->factory);
        $builder = new SingleColorPetrinetBuilder($this->factory);
        $this->initPlaces($builder);
        $this->initTransitions($builder);
        $this->initPetrinet($builder);
        $this->initAstar();
    }

    protected function initPlaces(SingleColorPetrinetBuilder $builder): void
    {
        $this->cartHasProducts = $builder->place();
        $this->cartEmpty = $builder->place();
        $this->checkout = $builder->place();
        $this->cartHasProducts->setId(0);
        $this->cartEmpty->setId(1);
        $this->checkout->setId(2);
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
        $this->goToCheckout = $builder->transition();
        $this->removeLastProduct = $builder->transition($hasOneProduct, $emptyProducts);
        $this->removeProduct = $builder->transition($hasMoreThanOneProduct, $removeProduct);
        $this->addFirstProduct = $builder->transition(null, $oneProduct);
        $this->addMoreProduct = $builder->transition(null, $addProduct);
        $this->updateCart = $builder->transition();
        $this->clearCart->setId(0);
        $this->goToCheckout->setId(1);
        $this->removeLastProduct->setId(2);
        $this->removeProduct->setId(3);
        $this->addFirstProduct->setId(4);
        $this->addMoreProduct->setId(5);
        $this->updateCart->setId(6);
    }

    protected function initPetrinet(SingleColorPetrinetBuilder $builder): void
    {
        $this->petrinet = $builder
            ->connect($this->cartHasProducts, $this->clearCart)
            ->connect($this->clearCart, $this->cartEmpty, 1)
            ->connect($this->cartHasProducts, $this->goToCheckout)
            ->connect($this->goToCheckout, $this->checkout)
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
            ->getPetrinet();
    }

    protected function initAstar(): void
    {
        $this->aStar = new PetrinetDomainLogic();
        $this->aStar->setTransitionService($this->transitionService);
        $this->aStar->setMarkingHelper($this->markingHelper);
        $this->aStar->setPetrinet($this->petrinet);
    }

    public function testRunFromCartEmptyToCheckout(): void
    {
        $start = new Node([$this->cartEmpty->getId() => 1], new Color(['products' => 0]), $this->clearCart->getId());
        $goal = new Node([$this->checkout->getId() => 1], new Color(['products' => 2]), $this->goToCheckout->getId());
        $nodes = $this->aStar->run($start, $goal);
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
                'transition' => $this->goToCheckout->getId(),
                'places' => [$this->checkout->getId() => 1],
                'color' => ['products' => 2],
            ],
        ], $nodes);
    }

    public function testRunFromCartHasProductsToCheckout(): void
    {
        $start = new Node(
            [$this->cartHasProducts->getId() => 1],
            new Color(['products' => 8]),
            $this->addMoreProduct->getId()
        );
        $goal = new Node([$this->checkout->getId() => 1], new Color(['products' => 1]), $this->goToCheckout->getId());
        $nodes = $this->aStar->run($start, $goal);
        $this->assertNodes([
            [
                'transition' => $this->addMoreProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => 8],
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
        ], $nodes);
    }

    protected function assertNodes(array $expectedNodes, array $nodes): void
    {
        foreach ($nodes as $index => $node) {
            $this->assertInstanceOf(Node::class, $node);
            /* @var Node $node */
            $this->assertSame($expectedNodes[$index]['transition'], $node->getTransition());
            $this->assertSame($expectedNodes[$index]['places'], $node->getPlaces());
            $this->assertSame($expectedNodes[$index]['color'], $node->getColor()->getValues());
        }
    }
}
