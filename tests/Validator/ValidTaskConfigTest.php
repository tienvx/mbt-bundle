<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfig;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidTaskConfig
 */
class ValidTaskConfigTest extends TestCase
{
    public function testGetTargets(): void
    {
        $taskConfig = new ValidTaskConfig();
        $this->assertSame('class', $taskConfig->getTargets());
    }
}
