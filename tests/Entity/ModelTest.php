<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\ToPlace;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\ToPlace
 * @covers \Tienvx\Bundle\MbtBundle\Validator\TagsValidator
 */
class ModelTest extends TestCase
{
    public function testPrePersist(): void
    {
        $model = new Model();
        $model->prePersist();
        $this->assertInstanceOf(\DateTime::class, $model->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $model->getUpdatedAt());
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
        $model->setPlaces([
            $p1 = new Place(),
            $p2 = new Place(),
        ]);
        $p1->setLabel('');
        $p1->setInit(true);
        $p1->setAssertions([
            $c1 = new Command(),
            $c2 = new Command(),
        ]);
        $c1->setCommand('');
        $c1->setTarget('css=.button');
        $c1->setValue(null);
        $c2->setCommand('click');
        $c2->setTarget('');
        $c2->setValue('test');
        $p2->setLabel('p2');
        $p1->setInit(false);
        $p2->setAssertions([
            $c3 = new Command(),
        ]);
        $c3->setCommand('');
        $c3->setTarget('');
        $c3->setValue(null);
        $model->setTransitions([
            $t1 = new Transition(),
            $t2 = new Transition(),
        ]);
        $t1->setLabel('t1');
        $t1->setFromPlaces([]);
        $t1->setToPlaces([
            $tp1 = new ToPlace(),
            $tp2 = new ToPlace(),
        ]);
        $tp1->setPlace(1);
        $tp1->setExpression('{count: count + 1}');
        $tp2->setPlace(2);
        $tp2->setExpression('{product: "Galaxy Note"}');
        $t2->setLabel('');
        $t2->setFromPlaces([1, 2]);
        $t2->setToPlaces([]);
        $t2->setGuard('count > 1');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($model);
        $this->assertCount(10, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Model).label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).tags:
    The tags should be unique and not blank. (code 628fca96-35f8-11eb-adc1-0242ac120002)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].assertions[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[0].assertions[1].target:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].assertions[0].command:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).places[1].assertions[0].target:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[0].fromPlaces:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].label:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Model).transitions[1].toPlaces:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }
}
