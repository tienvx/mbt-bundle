<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\TaskTest as TaskModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task\Browser
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 * @uses \Tienvx\Bundle\MbtBundle\Model\Debug
 */
class TaskTest extends TaskModelTest
{
    public function testPrePersist(): void
    {
        $this->task->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->task->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $this->task->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $this->task->prePersist();
        $this->task->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $updatedAt = $this->task->getUpdatedAt());
        $this->task->preUpdate();
        $this->assertTrue(
            $this->task->getUpdatedAt() instanceof \DateTime
            && $updatedAt !== $this->task->getUpdatedAt()
        );
    }

    public function testValidateInvalidBug(): void
    {
        $this->task->setTitle('');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($this->task);
        $this->assertCount(3, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Task).title:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Task).modelRevision.places:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
Object(Tienvx\Bundle\MbtBundle\Entity\Task).modelRevision.transitions:
    This collection should contain 1 element or more. (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    protected function createTask(): TaskInterface
    {
        return new Task();
    }
}
