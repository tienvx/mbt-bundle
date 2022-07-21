<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Facebook\WebDriver\WebDriverPoint;

abstract class AbstractMousePointCommand extends AbstractMouseCommand
{
    public static function getValueHelper(): string
    {
        return 'Point: x-coordinate,y-coordinate';
    }

    public function validateValue(?string $value): bool
    {
        return $value && 2 === count([$x, $y] = array_pad(explode(',', $value), 2, null)) && $x && $y;
    }

    protected function getPoint(string $value): WebDriverPoint
    {
        list($x, $y) = explode(',', $value);

        return new WebDriverPoint((int) $x, (int) $y);
    }
}
