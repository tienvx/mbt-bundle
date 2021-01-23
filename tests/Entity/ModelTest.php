<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Validator\CustomConstraintValidatorFactory;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\MouseCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\StoreCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\WindowCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommandValidator
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Validator\TagsValidator
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommand
 * @covers \Tienvx\Bundle\MbtBundle\ValueObject\Model\Command
 */
class ModelTest extends TestCase
{
    protected Model $model;
    protected ValidatorInterface $validator;

    protected function setUp(): void
    {
        $model = new Model();
        $model->setLabel('');
        $model->setTags('tag1,tag1,tag2,,tag3');
        $places = [
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
        $model->setPlaces($places);
        $transitions = [
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
        $model->setTransitions($transitions);

        $this->model = $model;

        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setConstraintValidatorFactory(new CustomConstraintValidatorFactory())
            ->getValidator();
    }

    public function testPrePersist(): void
    {
        $model = new Model();
        $model->prePersist();
        $this->assertInstanceOf(\DateTime::class, $model->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $model->getUpdatedAt());
        $this->assertSame(1, $model->getVersion());
    }

    public function testPreUpdate(): void
    {
        $model = new Model();
        $model->prePersist();
        $model->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $updatedAt = $model->getUpdatedAt());
        $model->preUpdate();
        $this->assertTrue($model->getUpdatedAt() instanceof \DateTime && $updatedAt !== $model->getUpdatedAt());
    }

    public function testValidateInvalidModel(): void
    {
        $violations = $this->validator->validate($this->model);
        $this->assertCount(14, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].toPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].fromPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions:
    mbt.model.missing_start_transition
Object(Tienvx\Bundle\MbtBundle\Entity\Model).label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).tags:
    The tags should be unique and not blank. (code 628fca96-35f8-11eb-adc1-0242ac120002)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[1].target:
    mbt.model.command.required_target (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].commands[1].value:
    mbt.model.command.required_value (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].guard:
    This value should be of type string.
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].toPlaces:
    mbt.model.missing_to_places (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    public function testValidateInvalidModelTooManyStartTransitions(): void
    {
        $transitions = $this->model->getTransitions();
        $transitions[0]->setFromPlaces([]);
        $transitions[1]->setFromPlaces([]);
        $this->model->setTransitions($transitions);
        $violations = $this->validator->validate($this->model);
        $this->assertCount(13, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].toPlaces:
    mbt.model.places_invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions:
    mbt.model.too_many_start_transitions
Object(Tienvx\Bundle\MbtBundle\Entity\Model).label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).tags:
    The tags should be unique and not blank. (code 628fca96-35f8-11eb-adc1-0242ac120002)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].commands[1].target:
    mbt.model.command.required_target (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].commands[0].command:
    mbt.model.command.invalid_command (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].commands[1].value:
    mbt.model.command.required_value (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].guard:
    This value should be of type string.
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].toPlaces:
    mbt.model.missing_to_places (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    public function testNormalize(): void
    {
        $this->assertSame([
            'label' => '',
            'tags' => 'tag1,tag1,tag2,,tag3',
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
                    'commands' => [
                    ],
                    'fromPlaces' => [
                        0 => 1,
                    ],
                    'toPlaces' => [
                        0 => 1,
                        1 => 2,
                    ],
                ],
                1 => [
                    'label' => '',
                    'guard' => 'count > 1',
                    'commands' => [
                    ],
                    'fromPlaces' => [
                        0 => 1,
                        1 => 2,
                    ],
                    'toPlaces' => [
                    ],
                ],
            ],
        ], $this->model->normalize());
    }

    public function testDenormalize(): void
    {
        $this->model->denormalize([
            'label' => 'Custom label',
            'tags' => 'custom,tags',
            'places' => [],
            'transitions' => [],
        ]);
        $this->assertSame('Custom label', $this->model->getLabel());
        $this->assertSame('custom,tags', $this->model->getTags());
        $this->assertSame([], $this->model->getPlaces());
        $this->assertSame([], $this->model->getTransitions());
    }
}
