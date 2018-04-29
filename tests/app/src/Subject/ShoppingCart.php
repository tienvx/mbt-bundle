<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Subject;

use Tienvx\Bundle\MbtBundle\Model\Subject;

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
     * @var string Temporary selected product from category
     */
    protected $selectedProductFromCategory;

    /**
     * @var string Temporary selected product from cart
     */
    protected $selectedProductFromCart;

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

    public function viewAnyCategoryFromHome()
    {
        $category = $this->data['category'] ?? $this->getRandomCategory();
        $this->category = $category;
        $this->product = null;
        $this->data = ['category' => $category];
    }

    public function viewOtherCategory()
    {
        $category = $this->data['category'] ?? $this->getRandomCategory();
        $this->category = $category;
        $this->product = null;
        $this->data = ['category' => $category];
    }

    public function viewAnyCategoryFromProduct()
    {
        $category = $this->data['category'] ?? $this->getRandomCategory();
        $this->category = $category;
        $this->product = null;
        $this->data = ['category' => $category];
    }

    public function viewAnyCategoryFromCart()
    {
        $category = $this->data['category'] ?? $this->getRandomCategory();
        $this->category = $category;
        $this->product = null;
        $this->data = ['category' => $category];
    }

    public function viewProductFromHome()
    {
        $product = $this->data['product'] ?? $this->getRandomProductFromHome();
        $this->product = $product;
        $this->category = null;
        $this->data = ['product' => $product];
    }

    public function viewProductFromCart()
    {
        $product = $this->selectedProductFromCart;
        $this->product = $product;
        $this->category = null;
        $this->data = ['product' => $product];
    }

    public function viewProductFromCategory()
    {
        $product = $this->selectedProductFromCategory;
        $this->product = $product;
        $this->category = null;
        $this->data = ['product' => $product];
    }

    public function categoryHasProduct()
    {
        $product = $this->data['product'] ?? $this->getRandomProductFromCategory();
        $this->selectedProductFromCategory = $product;
        return !empty($this->productsInCategory[$this->category]) &&
            in_array($product, $this->productsInCategory[$this->category]);
    }

    public function viewCartFromHome()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function viewCartFromCategory()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function viewCartFromProduct()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function viewCartFromCheckout()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function checkoutFromHome()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function checkoutFromCategory()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function checkoutFromProduct()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function checkoutFromCart()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function backToHomeFromCategory()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function backToHomeFromProduct()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function backToHomeFromCart()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function backToHomeFromCheckout()
    {
        $this->category = null;
        $this->product = null;
        $this->data = [];
    }

    public function cartHasProduct()
    {
        $product = $this->data['product'] ?? $this->getRandomProductFromCart();
        $this->selectedProductFromCart = $product;
        return !empty($this->cart[$product]);
    }

    public function addFromHome()
    {
        $product = $this->data['product'] ?? $this->getRandomProductFromHome();
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        }
        else {
            $this->cart[$product]++;
        }
        $this->data = ['product' => $product];
    }

    public function addFromCategory()
    {
        $product = $this->selectedProductFromCategory;
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        }
        else {
            $this->cart[$product]++;
        }
        $this->data = ['product' => $product];
    }

    public function addFromProduct()
    {
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        }
        else {
            $this->cart[$this->product]++;
        }
        $this->data = ['product' => $this->product];
    }

    public function remove()
    {
        $product = $this->selectedProductFromCart;
        unset($this->cart[$product]);
        $this->data = ['product' => $product];
    }

    public function update()
    {
        $product = $this->selectedProductFromCart;
        $this->cart[$product] = rand(1, 99);
        $this->data = ['product' => $product];
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
