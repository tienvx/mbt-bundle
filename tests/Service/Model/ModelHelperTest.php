<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 */
class ModelHelperTest extends TestCase
{
    protected ModelHelper $helper;
    protected ModelInterface $model;

    protected function setUp(): void
    {
        $this->helper = new ModelHelper();

        $this->model = new Model();
        $this->model->setId(1);
        $this->model->setPlaces([
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ]);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
            $transition3 = new Transition(),
            $transition4 = new Transition(),
        ];
        $transition1->setFromPlaces([0]);
        $transition1->setToPlaces([1]);
        $transition2->setFromPlaces([]);
        $transition2->setToPlaces([0]);
        $transition3->setFromPlaces([1]);
        $transition3->setToPlaces([2]);
        $transition4->setFromPlaces([1, 2]);
        $transition4->setToPlaces([1]);
        $this->model->setTransitions($transitions);
    }

    public function testGetStartTransitionId(): void
    {
        $this->assertSame(1, $this->helper->getStartTransitionId($this->model));
    }

    public function testGetStartPlaceIds(): void
    {
        $this->assertSame([0 => 1], $this->helper->getStartPlaceIds($this->model));
    }

    public function testGetStartTransitionIdMissingStartTransition(): void
    {
        $transitions = $this->model->getTransitions();
        $transitions[1]->setFromPlaces([1]);
        $this->model->setTransitions($transitions);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing start transition');
        $this->assertSame(1, $this->helper->getStartTransitionId($this->model));
    }

    public function testGetStartPlaceIdsMissingStartTransition(): void
    {
        $transitions = $this->model->getTransitions();
        $transitions[1]->setFromPlaces([1]);
        $this->model->setTransitions($transitions);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing start transition');
        $this->assertSame([0 => 1], $this->helper->getStartPlaceIds($this->model));
    }
}
