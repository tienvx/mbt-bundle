<?php

namespace Tienvx\Bundle\MbtBundle\Command\Mouse;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class MouseOutCommand extends AbstractMouseCommand
{
    public static function isTargetRequired(): bool
    {
        return true;
    }

    public static function isValueRequired(): bool
    {
        return false;
    }

    public function run(?string $target, ?string $value, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        parent::run($target, $value, $values, $driver);
        $element = $driver->findElement($this->getSelector($target));
        [$rect, $vp] = $driver->executeScript(
            // phpcs:ignore Generic.Files.LineLength
            'return [arguments[0].getBoundingClientRect(), {height: window.innerHeight, width: window.innerWidth}];',
            [$element]
        );
        if ($rect->top > 0) {
            // try top
            $y = -($rect->height / 2 + 1);
            $driver->getMouse()->mouseMove($element->getCoordinates(), null, $y);
        } elseif ($vp->width > $rect->right) {
            // try right
            $x = $rect->right / 2 + 1;
            $driver->getMouse()->mouseMove($element->getCoordinates(), $x);
        } elseif ($vp->height > $rect->bottom) {
            // try bottom
            $y = $rect->height / 2 + 1;
            $driver->getMouse()->mouseMove($element->getCoordinates(), null, $y);
        } elseif ($rect->left > 0) {
            // try left
            $x = (int) (-($rect->right / 2));
            $driver->getMouse()->mouseMove($element->getCoordinates(), $x);
        } else {
            throw new Exception(
                'Unable to perform mouse out as the element takes up the entire viewport'
            );
        }
    }
}
