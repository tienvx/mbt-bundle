<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Selenium\Helper;

interface SeleniumInterface
{
    public function setDsn(string $dsn): void;

    public function createHelper(): Helper;
}
