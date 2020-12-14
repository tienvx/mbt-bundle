<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfig;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Validator\ValidSeleniumConfig
 */
class ValidSeleniumConfigTest extends TestCase
{
    public function testGetTargets(): void
    {
        $seleniumConfig = new ValidSeleniumConfig();
        $this->assertSame('class', $seleniumConfig->getTargets());
    }
}
