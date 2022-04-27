<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CommandPreprocessor implements CommandPreprocessorInterface
{
    public function process(CommandInterface $command, ValuesInterface $values): CommandInterface
    {
        $processed = new Command();
        $processed->setCommand($command->getCommand());
        $processed->setTarget(
            $command->getTarget()
                ? $this->replaceVariables($command->getTarget(), $values->getValues())
                : $command->getTarget()
        );
        $processed->setValue(
            $command->getValue()
                ? $this->replaceVariables($command->getValue(), $values->getValues())
                : $command->getValue()
        );

        return $processed;
    }

    protected function replaceVariables(string $text, array $values): string
    {
        return preg_replace_callback(
            '/\$\{(.*?)\}/',
            fn (array $matches): string => $values[$matches[1]] ?? $matches[1],
            $text
        );
    }
}
