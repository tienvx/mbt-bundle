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
use SingleColorPetrinet\Service\ExpressionLanguageEvaluator;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\AStar
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\Node
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
    protected AStar $aStar;

    protected function setUp(): void
    {
        $this->factory = new ColorfulFactory();
        $expressionLanguage = new ExpressionLanguage();
        $expressionEvaluator = new ExpressionLanguageEvaluator($expressionLanguage);
        $this->transitionService = new GuardedTransitionService($this->factory, $expressionEvaluator);
        $this->markingHelper = new MarkingHelper($this->factory);
        $builder = new SingleColorPetrinetBuilder($this->factory);
        $this->petrinet = $builder
            ->connect($this->cartHasProducts = $builder->place(), $this->clearCart = $builder->transition())
            ->connect($this->clearCart, $this->cartEmpty = $builder->place(), 1, '{products: 0}')
            ->connect($this->cartHasProducts, $this->goToCheckout = $builder->transition())
            ->connect($this->goToCheckout, $this->checkout = $builder->place())
            ->connect($this->cartHasProducts, $this->removeLastProduct = $builder->transition('products === 1'))
            ->connect($this->removeLastProduct, $this->cartEmpty, 1, '{products: 0}')
            ->connect($this->cartHasProducts, $this->removeProduct = $builder->transition('products > 1'))
            ->connect($this->removeProduct, $this->cartHasProducts, 1, '{products: products - 1}')
            ->connect($this->cartEmpty, $this->addFirstProduct = $builder->transition())
            ->connect($this->addFirstProduct, $this->cartHasProducts, 1, '{products: 1}')
            ->connect($this->cartHasProducts, $this->addMoreProduct = $builder->transition())
            ->connect($this->addMoreProduct, $this->cartHasProducts, 1, '{products: products + 1}')
            ->connect($this->cartHasProducts, $this->updateCart = $builder->transition())
            ->connect($this->updateCart, $this->cartHasProducts)
            ->getPetrinet();
        $this->cartHasProducts->setId(0);
        $this->cartEmpty->setId(1);
        $this->checkout->setId(2);
        $this->clearCart->setId(0);
        $this->goToCheckout->setId(1);
        $this->removeLastProduct->setId(2);
        $this->removeProduct->setId(3);
        $this->addFirstProduct->setId(4);
        $this->addMoreProduct->setId(5);
        $this->updateCart->setId(6);
        $this->aStar = new AStar();
        $this->aStar->setTransitionService($this->transitionService);
        $this->aStar->setMarkingHelper($this->markingHelper);
        $this->aStar->setPetrinet($this->petrinet);
    }

    public function testRunFromCartEmptyToCheckout(): void
    {
        $start = new Node([$this->cartEmpty->getId() => 1], new Color(['products' => 0]), null);
        $goal = new Node([$this->checkout->getId() => 1], new Color(['products' => 2]), 1);
        $nodes = $this->aStar->run($start, $goal);
        $this->assertNodes([
            [
                'transition' => null,
                'places' => [$this->cartEmpty->getId() => 1],
                'color' => ['products' => '0'],
            ],
            [
                'transition' => $this->addFirstProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => '1'],
            ],
            [
                'transition' => $this->addMoreProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => '2'],
            ],
            [
                'transition' => $this->goToCheckout->getId(),
                'places' => [$this->checkout->getId() => 1],
                'color' => ['products' => '2'],
            ],
        ], $nodes);
    }

    public function testRunFromCartHasProductsToCheckout(): void
    {
        $start = new Node([$this->cartHasProducts->getId() => 1], new Color(['products' => 8]), null);
        $goal = new Node([$this->checkout->getId() => 1], new Color(['products' => 1]), 1);
        $nodes = $this->aStar->run($start, $goal);
        $this->assertNodes([
            [
                'transition' => null,
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => '8'],
            ],
            [
                'transition' => $this->clearCart->getId(),
                'places' => [$this->cartEmpty->getId() => 1],
                'color' => ['products' => '0'],
            ],
            [
                'transition' => $this->addFirstProduct->getId(),
                'places' => [$this->cartHasProducts->getId() => 1],
                'color' => ['products' => '1'],
            ],
            [
                'transition' => $this->goToCheckout->getId(),
                'places' => [$this->checkout->getId() => 1],
                'color' => ['products' => '1'],
            ],
        ], $nodes);
    }

    protected function assertNodes(array $expectedNodes, array $nodes): void
    {
        foreach ($nodes as $index => $node) {
            $this->assertInstanceOf(Node::class, $node);
            /** @var Node $node */
            $this->assertSame($expectedNodes[$index]['transition'], $node->getTransition());
            $this->assertSame($expectedNodes[$index]['places'], $node->getPlaces());
            $this->assertSame($expectedNodes[$index]['color'], $node->getColor()->getValues());
        }
    }
}
