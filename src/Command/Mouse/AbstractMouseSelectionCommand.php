<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

abstract class AbstractMouseSelectionCommand extends AbstractMouseCommand
{
    public function validateValue(?string $value): bool
    {
        return $value && in_array(substr($value, 0, 6), ['index=', 'value=', 'label=']);
    }
}
