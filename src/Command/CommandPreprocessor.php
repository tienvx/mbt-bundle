<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class CommandPreprocessor implements CommandPreprocessorInterface
{
    public function process(CommandInterface $command, ColorInterface $color): CommandInterface
    {
        $processed = new Command();
        $processed->setCommand($command->getCommand());
        $processed->setTarget($this->replaceVariables($command->getTarget(), $color->getValues()));
        $processed->setValue($this->replaceVariables($command->getValue(), $color->getValues()));

        return $processed;
    }

    protected function replaceVariables(string $text, array $values): string
    {
        return preg_replace_callback('/\$\{(.*?)\}/', function ($matches) use ($values) {
            return $values[$matches[1]] ?? $matches[1];
        }, $text);
    }
}
