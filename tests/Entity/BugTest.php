<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\BugTest as BugModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Progress
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class BugTest extends BugModelTest
{
    public function testPrePersist(): void
    {
        $this->bug->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->bug->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $this->bug->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $this->bug->prePersist();
        $this->bug->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $updatedAt = $this->bug->getUpdatedAt());
        $this->bug->preUpdate();
        $this->assertTrue(
            $this->bug->getUpdatedAt() instanceof \DateTime
            && $updatedAt !== $this->bug->getUpdatedAt()
        );
    }

    public function testValidateInvalidBug(): void
    {
        $this->bug->setTitle('');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($this->bug);
        $this->assertCount(3, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Bug).title:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
Object(Tienvx\Bundle\MbtBundle\Entity\Bug).steps[0].places:
    mbt.bug.missing_places_in_step (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
Object(Tienvx\Bundle\MbtBundle\Entity\Bug).steps[1].places:
    mbt.bug.missing_places_in_step (code bef8e338-6ae5-4caf-b8e2-50e7b0579e69)
';
        $this->assertSame($message, (string) $violations);
    }

    protected function createBug(): BugInterface
    {
        return new Bug();
    }
}
