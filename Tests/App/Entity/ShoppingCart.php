<?php

namespace Tienvx\Bundle\MbtBundle\Tests\App\Entity;

class ShoppingCart
{
    /**
     * @var string Required by workflow component
     */
    public $marking;

    /**
     * @var array
     */
    protected $cart;

    /**
     * @var string
     */
    protected $product;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var array
     */
    protected $categories = [
        '24' => 'Phones & PDAs',
        '17' => 'Software',
        '20_27' => 'Mac',
        '25_28' => 'Monitors',
        '33' => 'Cameras',
        '20' => 'Desktops'
    ];

    /**
     * @var array
     */
    protected $products = [
        '28' => 'HTC Touch HD',
        '40' => 'iPhone',
        '29' => 'Palm Treo Pro',
        '41' => 'iMac',
        '42' => 'Apple Cinema 30',
        '33' => 'Samsung SyncMaster 941BW',
        '30' => 'Canon EOS 5D',
        '31' => 'Nikon D300',
        '43' => 'MacBook',
    ];

    /**
     * @var array
     */
    protected $productsInCategory = [
        '24' => [
            '28' => 'HTC Touch HD',
            '40' => 'iPhone',
            '29' => 'Palm Treo Pro',
        ],
        '17' => [],
        '20_27' => [
            '41' => 'iMac',
        ],
        '25_28' => [
            '42' => 'Apple Cinema 30',
            '33' => 'Samsung SyncMaster 941BW'
        ],
        '33' => [
            '30' => 'Canon EOS 5D',
            '31' => 'Nikon D300'
        ],
        '20' => [
            '43' => 'MacBook'
        ]
    ];

    /**
     * @var array
     */
    protected $inStock = [
        '40', // 'iPhone',
        '42', //'Apple Cinema 30',
        '30', //'Canon EOS 5D',
        '43', //'MacBook',
    ];

    public function __construct()
    {
        $this->cart = [];
        $this->category = null;
        $this->product = null;
    }

    public function viewCategory()
    {
        $this->category = array_rand($this->categories);
    }

    public function viewProduct()
    {
        $this->product = array_rand($this->products);
    }

    public function viewProductFromCategory()
    {
        $this->product = array_rand($this->productsInCategory[$this->category]);
    }

    public function categoryHasProduct()
    {
        return !empty($this->productsInCategory[$this->category]);
    }

    public function cartHasProduct()
    {
        return !empty($this->cart);
    }

    public function addFromHome()
    {
        $this->product = array_rand($this->products);
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        }
        else {
            $this->cart[$this->product]++;
        }
    }

    public function addFromCategory()
    {
        $this->product = array_rand($this->productsInCategory[$this->category]);
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        }
        else {
            $this->cart[$this->product]++;
        }
    }

    public function productOnLeave()
    {
        $this->product = null;
    }

    public function categoryOnLeave()
    {
        $this->category = null;
    }

    public function viewingProduct()
    {
        return !empty($this->product);
    }

    public function addFromProduct()
    {
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        }
        else {
            $this->cart[$this->product]++;
        }
    }

    public function remove()
    {
        $product = array_rand($this->cart);
        unset($this->cart[$product]);
    }

    public function update()
    {
        $product = array_rand($this->cart);
        if (!in_array($product, $this->inStock)) {
            throw new \Exception('You added a out-of-stock product into cart! We can not update this product');
        }
        $this->cart[$product] = rand(1, 99);
    }
}
