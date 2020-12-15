<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Tienvx\Bundle\MbtBundle\Entity\Progress;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Progress
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class ProgressTest extends TestCase
{
    public function testValidateInvalidProgress(): void
    {
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(11);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validate($progress);
        $this->assertCount(1, $violations);
        $message = 'Object(Tienvx\Bundle\MbtBundle\Entity\Progress).processed:
    Processed should be less than or equal to total.
';
        $this->assertSame($message, (string) $violations);
    }
}
