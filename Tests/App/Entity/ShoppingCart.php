<?php

namespace Tienvx\Bundle\MbtBundle\Tests\App\Entity;

class ShoppingCart
{
    /**
     * @var int
     */
    protected $number;

    public function __construct()
    {
        $this->number = 0;
    }
}
