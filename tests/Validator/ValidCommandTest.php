<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Validator\ValidCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidCommand
 */
class ValidCommandTest extends TestCase
{
    public function testGetTargets(): void
    {
        $constraint = new ValidCommand();
        $this->assertSame('class', $constraint->getTargets());
    }
}
