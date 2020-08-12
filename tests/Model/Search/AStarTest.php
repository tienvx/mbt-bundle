<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Search;

use Petrinet\Builder\MarkingBuilder;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Builder\SingleColorPetrinetBuilder;
use SingleColorPetrinet\Model\ColorfulFactoryInterface;
use SingleColorPetrinet\Service\ExpressionLanguageEvaluator;
use SingleColorPetrinet\Service\GuardedTransitionService;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;
use Tienvx\Bundle\MbtBundle\Model\Search\AStar;
use Tienvx\Bundle\MbtBundle\Model\Search\Node;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Factory;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\AStar
 * @covers \Tienvx\Bundle\MbtBundle\Model\Search\Node
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Marking
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceMarking
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Token
 * @covers \Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 */
class AStarTest extends TestCase
{
    protected ColorfulFactoryInterface $factory;
    protected GuardedTransitionServiceInterface $transitionService;
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

    protected function setUp(): void
    {
        $this->factory = Factory::createColorfulFactory();
        $expressionLanguage = new ExpressionLanguage();
        $expressionEvaluator = new ExpressionLanguageEvaluator($expressionLanguage);
        $this->transitionService = new GuardedTransitionService($this->factory, $expressionEvaluator);
        $builder = new SingleColorPetrinetBuilder($this->factory);
        $this->petrinet = $builder
            ->connect($this->cartHasProducts = $builder->place(), $this->clearCart = $builder->transition())
            ->connect($this->clearCart, $this->cartEmpty = $builder->place(), 1, $this->factory->createExpression('{products: 0}'))
            ->connect($this->cartHasProducts, $this->goToCheckout = $builder->transition())
            ->connect($this->goToCheckout, $this->checkout = $builder->place())
            ->connect($this->cartHasProducts, $this->removeLastProduct = $builder->transition($this->factory->createExpression('products === 1')))
            ->connect($this->removeLastProduct, $this->cartEmpty, 1, $this->factory->createExpression('{products: 0}'))
            ->connect($this->cartHasProducts, $this->removeProduct = $builder->transition($this->factory->createExpression('products > 1')))
            ->connect($this->removeProduct, $this->cartHasProducts, 1, $this->factory->createExpression('{products: products - 1}'))
            ->connect($this->cartEmpty, $this->addFirstProduct = $builder->transition())
            ->connect($this->addFirstProduct, $this->cartHasProducts, 1, $this->factory->createExpression('{products: 1}'))
            ->connect($this->cartHasProducts, $this->addMoreProduct = $builder->transition())
            ->connect($this->addMoreProduct, $this->cartHasProducts, 1, $this->factory->createExpression('{products: products + 1}'))
            ->connect($this->cartHasProducts, $this->updateCart = $builder->transition())
            ->connect($this->updateCart, $this->cartHasProducts)
            ->getPetrinet();
        $this->cartEmpty->setId(1);
        $this->cartHasProducts->setId(2);
        $this->checkout->setId(3);
        $this->clearCart->setId(1);
        $this->goToCheckout->setId(2);
        $this->removeLastProduct->setId(3);
        $this->removeProduct->setId(4);
        $this->addFirstProduct->setId(5);
        $this->addMoreProduct->setId(6);
        $this->updateCart->setId(7);
    }

    public function testRunFromCartEmptyToCheckout(): void
    {
        $color1 = $this->factory->createColor([
            'products' => 0,
        ]);
        $markingBuilder1 = new MarkingBuilder($this->factory);
        $marking1 = $markingBuilder1
            ->mark($this->cartEmpty, 1)
            ->getMarking();
        $marking1->setColor($color1);
        $color2 = $this->factory->createColor([
            'products' => 2,
        ]);
        $markingBuilder2 = new MarkingBuilder($this->factory);
        $marking2 = $markingBuilder2
            ->mark($this->checkout, 1)
            ->getMarking();
        $marking2->setColor($color2);

        $aStar = new AStar();
        $aStar->setTransitionService($this->transitionService);
        $aStar->setPetrinet($this->petrinet);
        $start = new Node($marking1, null);
        $goal = new Node($marking2, $this->goToCheckout);
        $nodes = $aStar->run($start, $goal);
        $this->assertInstanceOf(Node::class, $nodes[0]);
        $this->assertSame(null, $nodes[0]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[1]);
        $this->assertSame($this->addFirstProduct, $nodes[1]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[2]);
        $this->assertSame($this->addMoreProduct, $nodes[2]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[3]);
        $this->assertSame($this->goToCheckout, $nodes[3]->getTransition());
    }

    public function testRunFromCartHasProductsToCheckout(): void
    {
        $color1 = $this->factory->createColor([
            'products' => 8,
        ]);
        $markingBuilder1 = new MarkingBuilder($this->factory);
        $marking1 = $markingBuilder1
            ->mark($this->cartHasProducts, 1)
            ->getMarking();
        $marking1->setColor($color1);
        $color2 = $this->factory->createColor([
            'products' => 1,
        ]);
        $markingBuilder2 = new MarkingBuilder($this->factory);
        $marking2 = $markingBuilder2
            ->mark($this->checkout, 1)
            ->getMarking();
        $marking2->setColor($color2);

        $aStar = new AStar();
        $aStar->setTransitionService($this->transitionService);
        $aStar->setPetrinet($this->petrinet);
        $start = new Node($marking1, null);
        $goal = new Node($marking2, $this->goToCheckout);
        $nodes = $aStar->run($start, $goal);
        $this->assertInstanceOf(Node::class, $nodes[0]);
        $this->assertSame(null, $nodes[0]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[1]);
        $this->assertSame($this->clearCart, $nodes[1]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[2]);
        $this->assertSame($this->addFirstProduct, $nodes[2]->getTransition());
        $this->assertInstanceOf(Node::class, $nodes[3]);
        $this->assertSame($this->goToCheckout, $nodes[3]->getTransition());
    }
}
