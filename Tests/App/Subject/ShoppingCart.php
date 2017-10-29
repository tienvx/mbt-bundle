<?php

namespace Tienvx\Bundle\MbtBundle\Tests\App\Subject;

use Tienvx\Bundle\MbtBundle\Subject\Subject;

class ShoppingCart extends Subject
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
     * @var string Current viewing product
     */
    protected $product;

    /**
     * @var string Current viewing category
     */
    protected $category;

    /**
     * @var array
     */
    protected $products = [
        '28', // 'HTC Touch HD',
        '40', // 'iPhone',
        '29', // 'Palm Treo Pro',
        '41', // 'iMac',
        '42', // 'Apple Cinema 30',
        '33', // 'Samsung SyncMaster 941BW',
        '30', // 'Canon EOS 5D',
        '31', // 'Nikon D300',
        '43', // 'MacBook',
    ];

    /**
     * @var array
     */
    protected $categories = [
        '24', // 'Phones & PDAs',
        '17', // 'Software',
        '20_27', // 'Mac',
        '25_28', // 'Monitors',
        '33', // 'Cameras',
        '20', // 'Desktops'
    ];

    /**
     * @var array
     */
    protected $productsInCategory = [
        '24' => [
            '28', // 'HTC Touch HD',
            '40', // 'iPhone',
            '29', // 'Palm Treo Pro',
        ],
        '17' => [],
        '20_27' => [
            '41', // 'iMac',
        ],
        '25_28' => [
            '42', // 'Apple Cinema 30',
            '33', // 'Samsung SyncMaster 941BW'
        ],
        '33' => [
            '30', // 'Canon EOS 5D',
            '31', // 'Nikon D300'
        ],
        '20' => [
            '43', // 'MacBook'
        ]
    ];

    /**
     * @var array
     */
    protected $stock = [
        // No products are available in stock.
    ];

    public function __construct()
    {
        $this->cart = [];
        $this->category = null;
        $this->product = null;
        parent::__construct();
    }

    /**
     * @param $data array
     */
    public function viewAnyCategoryFromHome($data)
    {
        $category = $data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @param $data array
     */
    public function viewOtherCategory($data)
    {
        $category = $data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @param $data array
     */
    public function viewAnyCategoryFromProduct($data)
    {
        $category = $data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @param $data array
     */
    public function viewAnyCategoryFromCart($data)
    {
        $category = $data['category'];
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @param $data array
     */
    public function viewProductFromHome($data)
    {
        $product = $data['product'];
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @param $data array
     */
    public function viewProductFromCart($data)
    {
        $product = $data['product'];
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @param $data array
     */
    public function viewProductFromCategory($data)
    {
        $product = $data['product'];
        $this->product = $product;
        $this->category = null;
    }

    public function categoryHasProduct()
    {
        return !empty($this->productsInCategory[$this->category]);
    }

    public function viewCartFromHome()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function viewCartFromCheckout()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromHome()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function checkoutFromCart()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCategory()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromProduct()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCart()
    {
        $this->category = null;
        $this->product = null;
    }

    public function backToHomeFromCheckout()
    {
        $this->category = null;
        $this->product = null;
    }

    public function cartHasProduct()
    {
        return !empty($this->cart);
    }

    /**
     * @param $data array
     */
    public function addFromHome($data)
    {
        $product = $data['product'];
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        }
        else {
            $this->cart[$product]++;
        }
    }

    /**
     * @param $data array
     */
    public function addFromCategory($data)
    {
        $product = $data['product'];
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        }
        else {
            $this->cart[$product]++;
        }
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

    /**
     * @param $data array
     */
    public function remove($data)
    {
        $product = $data['product'];
        unset($this->cart[$product]);
    }

    /**
     * @param $data array
     * @throws \Exception
     */
    public function update($data)
    {
        $product = $data['product'];
        if ($this->callSUT && !in_array($product, $this->stock)) {
            throw new \Exception('You added an out-of-stock product into cart! It can not be updated');
        }
        $this->cart[$product] = rand(1, 99);
    }

    public function home()
    {
    }

    public function category()
    {
    }

    public function product()
    {
    }

    public function cart()
    {
    }

    public function checkout()
    {
    }

    public function getRandomProduct()
    {
        $product = array_rand($this->products);
        return $product;
    }

    public function getRandomCategory()
    {
        $category = array_rand($this->categories);
        return $category;
    }

    public function getRandomProductFromCart()
    {
        $product = array_rand($this->cart);
        return $product;
    }

    public function getRandomProductFromCategory()
    {
        $product = array_rand($this->productsInCategory[$this->category]);
        return $product;
    }
}
