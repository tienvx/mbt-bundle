<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

interface CommandPreprocessorInterface
{
    public function process(CommandInterface $command, ValuesInterface $values): CommandInterface;
}
