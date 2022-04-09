<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Tests\Model\ProgressTest as ProgressModelTest;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Progress
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class ProgressTest extends ProgressModelTest
{
    public function testValidateInvalidProgress(): void
    {
        $this->progress->setTotal(10);
        $this->progress->setProcessed(11);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($this->progress);
        $this->assertCount(1, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Progress).processed:
    Processed should be less than or equal to total.
';
        $this->assertSame($message, (string) $violations);
    }

    protected function createProgress(): ProgressInterface
    {
        return new Progress();
    }
}
