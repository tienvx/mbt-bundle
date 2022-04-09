<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Model\Model;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Model\Command
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Model\Place
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition
 */
class RevisionTest extends TestCase
{
    protected ModelInterface $model;
    protected RevisionInterface $revision;
    protected ValidatorInterface $validator;
    protected array $places;
    protected array $transitions;

    protected function setUp(): void
    {
        $this->revision = $this->createRevision();
        $this->revision->setId(1);
        $this->places = [
            $p1 = new Place(),
            $p2 = new Place(),
        ];
        $p1->setLabel('');
        $p1->setCommands([
            $c1 = new Command(),
            $c2 = new Command(),
        ]);
        $c1->setCommand('');
        $c1->setTarget('css=.name');
        $c1->setValue('test');
        $c2->setCommand('click');
        $c2->setTarget(null);
        $c2->setValue('test');
        $p2->setLabel('p2');
        $p2->setCommands([
            $c3 = new Command(),
            $c4 = new Command(),
        ]);
        $c3->setCommand('doNoThing');
        $c3->setTarget('css=.about');
        $c3->setValue('test');
        $c4->setCommand('clickAt');
        $c4->setTarget('css=.avatar');
        $c4->setValue(null);
        $this->revision->setPlaces($this->places);
        $this->transitions = [
            $t1 = new Transition(),
            $t2 = new Transition(),
        ];
        $t1->setLabel('t1');
        $t1->setFromPlaces([1]);
        $t1->setToPlaces([1, 2]);
        $t2->setLabel('');
        $t2->setFromPlaces([1, 2]);
        $t2->setToPlaces([]);
        $t2->setGuard('count > 1');
        $this->revision->setTransitions($this->transitions);
        $this->model = new Model();
        $this->model->setActiveRevision(new Revision());
        $this->revision->setModel($this->model);
    }

    public function testProperties(): void
    {
        $this->assertSame(1, $this->revision->getId());
        $this->assertSame($this->model, $this->revision->getModel());
        $this->assertSame($this->places, $this->revision->getPlaces());
        $this->assertSame($this->places[0], $this->revision->getPlace(0));
        $this->assertSame($this->transitions, $this->revision->getTransitions());
        $this->assertSame($this->transitions[0], $this->revision->getTransition(0));
    }

    public function testConvertToString(): void
    {
        $this->assertSame('', (string) $this->revision);
        $this->model->setLabel('model label');
        $this->assertSame('model label', (string) $this->revision);
    }

    public function testIsLatest(): void
    {
        $this->assertFalse($this->revision->isLatest());
        $this->model->setActiveRevision($this->revision);
        $this->assertTrue($this->revision->isLatest());
    }

    public function testToArray(): void
    {
        $this->assertSame([
            'places' => [
                0 => [
                    'label' => '',
                    'commands' => [
                        0 => [
                            'command' => '',
                            'target' => 'css=.name',
                            'value' => 'test',
                        ],
                        1 => [
                            'command' => 'click',
                            'target' => null,
                            'value' => 'test',
                        ],
                    ],
                ],
                1 => [
                    'label' => 'p2',
                    'commands' => [
                        0 => [
                            'command' => 'doNoThing',
                            'target' => 'css=.about',
                            'value' => 'test',
                        ],
                        1 => [
                            'command' => 'clickAt',
                            'target' => 'css=.avatar',
                            'value' => null,
                        ],
                    ],
                ],
            ],
            'transitions' => [
                0 => [
                    'label' => 't1',
                    'guard' => null,
                    'fromPlaces' => [
                        0 => 1,
                    ],
                    'toPlaces' => [
                        0 => 1,
                        1 => 2,
                    ],
                    'commands' => [
                    ],
                ],
                1 => [
                    'label' => '',
                    'guard' => 'count > 1',
                    'fromPlaces' => [
                        0 => 1,
                        1 => 2,
                    ],
                    'toPlaces' => [
                    ],
                    'commands' => [
                    ],
                ],
            ],
        ], $this->revision->toArray());
    }

    protected function createRevision(): RevisionInterface
    {
        return new Revision();
    }
}
