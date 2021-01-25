<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Model;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 */
class ModelHelperTest extends TestCase
{
    protected ModelHelper $helper;
    protected RevisionInterface $revision;

    protected function setUp(): void
    {
        $this->helper = new ModelHelper();

        $this->revision = new Revision();
        $this->revision->setPlaces(
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        );
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
        $this->revision->setTransitions(...$transitions);
    }

    public function testGetStartTransitionId(): void
    {
        $this->assertSame(1, $this->helper->getStartTransitionId($this->revision));
    }

    public function testGetStartPlaceIds(): void
    {
        $this->assertSame([0 => 1], $this->helper->getStartPlaceIds($this->revision));
    }

    public function testGetStartTransitionIdMissingStartTransition(): void
    {
        $this->revision->getTransition(1)->setFromPlaces([1]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing start transition');
        $this->assertSame(1, $this->helper->getStartTransitionId($this->revision));
    }

    public function testGetStartPlaceIdsMissingStartTransition(): void
    {
        $this->revision->getTransition(1)->setFromPlaces([1]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing start transition');
        $this->assertSame([0 => 1], $this->helper->getStartPlaceIds($this->revision));
    }
}
