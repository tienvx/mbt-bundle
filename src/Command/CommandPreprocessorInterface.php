<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandPreprocessorInterface
{
    public function process(CommandInterface $command, ColorInterface $color): CommandInterface;
}
