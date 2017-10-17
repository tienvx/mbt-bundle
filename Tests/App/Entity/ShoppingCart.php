<?php

namespace Tienvx\Bundle\MbtBundle\Tests\App\Entity;

class ShoppingCart
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var int
     */
    protected $number;

    public function __construct()
    {
        $this->number = 0;
    }
}
