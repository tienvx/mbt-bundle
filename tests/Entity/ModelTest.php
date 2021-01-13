<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
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
        $model = new Model();
        $model->setLabel('');
        $model->setTags('tag1,tag1,tag2,,tag3');
        $model->setStartUrl('');
        $places = [
            $p1 = new Place(),
            $p2 = new Place(),
        ];
        $p1->setLabel('');
        $p1->setStart(false);
        $p1->setAssertions([
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
        $p2->setStart(false);
        $p2->setAssertions([
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
        $t1->setFromPlaces([]);
        $t1->setToPlaces([1, 2]);
        $t2->setLabel('');
        $t2->setFromPlaces([1, 2]);
        $t2->setToPlaces([]);
        $t2->setGuard('count > 1');
        $model->setTransitions($transitions);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setConstraintValidatorFactory(new CustomConstraintValidatorFactory())
            ->getValidator();
        $violations = $validator->validate($model);
        $this->assertCount(16, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].toPlaces:
    To places are invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].fromPlaces:
    From places are invalid
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places:
    You must select at least 1 start place
Object(Tienvx\Bundle\MbtBundle\Entity\Model).label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).tags:
    The tags should be unique and not blank. (code 628fca96-35f8-11eb-adc1-0242ac120002)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).startUrl:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].assertions[0].command:
    The command is not valid. (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].assertions[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].assertions[1].target:
    The target is not valid. (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].assertions[0].command:
    The command is not valid. (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].assertions[1].value:
    The value is not valid. (code ba5fd751-cbdf-45ab-a1e7-37045d5ef44b)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].guard:
    This value should be of type string.
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].fromPlaces:
    This transition should connect at least 1 place to other places. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].toPlaces:
    This transition should connect some places to at least 1 place. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }
}
