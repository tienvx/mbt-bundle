<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Subject;

use Tienvx\Bundle\MbtBundle\Subject\Subject;

class ShoppingCart extends Subject
{
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
    protected $featuredProducts = [
        '43', // 'MacBook',
        '40', // 'iPhone',
        '42', // 'Apple Cinema 30',
        '30', // 'Canon EOS 5D',
    ];

    /**
     * @var array
     */
    protected $categories = [
        '20', // 'Desktops',
        '20_27', // 'Mac',
        '18', // 'Laptops & Notebooks',
        '25', // 'Components',
        '25_28', // 'Monitors',
        '57', // 'Tablets',
        '17', // 'Software',
        '24', // 'Phones & PDAs',
        '33', // 'Cameras',
        '34', // 'MP3 Players',
    ];

    /**
     * @var array
     */
    protected $productsInCategory = [
        '20' => [
            '42', // 'Apple Cinema 30',
            '30', // 'Canon EOS 5D',
            '47', // 'HP LP3065',
            '28', // 'HTC Touch HD',
            '40', // 'iPhone',
            '48', // 'iPod Classic',
            '43', // 'MacBook',
            '42', // 'Apple Cinema 30',
            '44', // 'MacBook Air',
            '29', // 'Palm Treo Pro',
            '35', // 'Product 8',
            '33', // 'Samsung SyncMaster 941BW',
            '46', // 'Sony VAIO',
        ],
        '20_27' => [
            '41', // 'iMac',
        ],
        '18' => [
            '47', // 'HP LP3065',
            '43', // 'MacBook',
            '44', // 'MacBook Air',
            '45', // 'MacBook Pro',
            '46', // 'Sony VAIO',
        ],
        '25' => [],
        '25_28' => [
            '42', // 'Apple Cinema 30',
            '33', // 'Samsung SyncMaster 941BW'
        ],
        '57' => [
            '49', // 'Samsung Galaxy Tab 10.1',
        ],
        '17' => [],
        '24' => [
            '28', // 'HTC Touch HD',
            '40', // 'iPhone',
            '29', // 'Palm Treo Pro',
        ],
        '33' => [
            '30', // 'Canon EOS 5D',
            '31', // 'Nikon D300'
        ],
        '34' => [
            '48', // 'iPod Classic',
            '36', // 'iPod Nano',
            '34', // 'iPod Shuffle',
            '32', // 'iPod Touch',
        ],
    ];

    /**
     * @var array
     */
    protected $outOfStock = [
        '49', // 'Samsung Galaxy Tab 10.1',
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

    public function categoryHasProduct($data)
    {
        if (!isset($data['product'])) {
            return false;
        }
        $product = $data['product'];
        return !empty($this->productsInCategory[$this->category]) &&
            in_array($product, $this->productsInCategory[$this->category]);
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

    public function cartHasProduct($data)
    {
        if (!isset($data['product'])) {
            return false;
        }
        $product = $data['product'];
        return !empty($this->cart[$product]);
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
        if ($this->callSUT) {
            foreach ($this->cart as $product => $quantity) {
                if (in_array($product, $this->outOfStock)) {
                    throw new \Exception('You added an out-of-stock product into cart! Can not checkout');
                }
            }
        }
    }

    public function getRandomProductFromHome()
    {
        if (empty($this->featuredProducts)) {
            return null;
        }
        $product = $this->featuredProducts[array_rand($this->featuredProducts)];
        return $product;
    }

    public function getRandomCategory()
    {
        if (empty($this->categories)) {
            return null;
        }
        $category = $this->categories[array_rand($this->categories)];
        return $category;
    }

    public function getRandomProductFromCart()
    {
        if (empty($this->cart)) {
            return null;
        }
        $product = array_rand($this->cart);
        return $product;
    }

    public function getRandomProductFromCategory()
    {
        if (!isset($this->productsInCategory[$this->category])) {
            return null;
        }
        $products = $this->productsInCategory[$this->category];
        if (empty($products)) {
            return null;
        }
        $product = $products[array_rand($products)];
        return $product;
    }
}
