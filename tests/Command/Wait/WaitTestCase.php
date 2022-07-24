<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

abstract class WaitTestCase extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Seconds';
    protected string $group = 'wait';

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['css=#selector', true],
        ];
    }

    public function valueProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['123', true],
        ];
    }
}
