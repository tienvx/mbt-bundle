<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class BugTest extends TestCase
{
    public function testPrePersist(): void
    {
        $bug = new Bug();
        $bug->prePersist();
        $this->assertInstanceOf(\DateTime::class, $bug->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $bug->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $bug = new Bug();
        $bug->prePersist();
        $bug->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $bug->getUpdatedAt());
    }

    public function testValidateInvalidBug(): void
    {
        $bug = new Bug();
        $bug->setTitle('');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($bug);
        $this->assertCount(1, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Bug).title:
    This value should not be blank. (code c1051bb4-d103-4f74-8988-acbcafc7fdc3)
';
        $this->assertSame($message, (string) $violations);
    }
}
