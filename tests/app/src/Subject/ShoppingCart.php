<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class ShoppingCart extends AbstractSubject
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

    /**
     * @var array
     */
    protected $needOptions = [
        '42', // 'Apple Cinema 30',
        '30', // 'Canon EOS 5D',
        '35', // 'Product 8',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->cart = [];
        $this->category = null;
        $this->product = null;
    }

    public static function getName(): string
    {
        return 'shopping_cart';
    }

    /**
     * @Transition("viewAnyCategoryFromHome")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromHome(StepData $data)
    {
        if ($data->has('category')) {
            $category = $data->get('category');
            if (!in_array($category, $this->categories)) {
                throw new Exception('Selected category is invalid');
            }
        } else {
            $category = $this->categories[array_rand($this->categories)];
            $data->set('category', $category);
        }
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewOtherCategory")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewOtherCategory(StepData $data)
    {
        if ($data->has('category')) {
            $category = $data->get('category');
            if (!in_array($category, $this->categories)) {
                throw new Exception('Selected category is invalid');
            }
        } else {
            $category = $this->categories[array_rand($this->categories)];
            $data->set('category', $category);
        }
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewAnyCategoryFromProduct")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromProduct(StepData $data)
    {
        if ($data->has('category')) {
            $category = $data->get('category');
            if (!in_array($category, $this->categories)) {
                throw new Exception('Selected category is invalid');
            }
        } else {
            $category = $this->categories[array_rand($this->categories)];
            $data->set('category', $category);
        }
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewAnyCategoryFromCart")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromCart(StepData $data)
    {
        if ($data->has('category')) {
            $category = $data->get('category');
            if (!in_array($category, $this->categories)) {
                throw new Exception('Selected category is invalid');
            }
        } else {
            $category = $this->categories[array_rand($this->categories)];
            $data->set('category', $category);
        }
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewProductFromHome")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewProductFromHome(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (!in_array($product, $this->featuredProducts)) {
                throw new Exception('Selected product is not in featured products list');
            }
        } else {
            $product = $this->featuredProducts[array_rand($this->featuredProducts)];
            $data->set('product', $product);
        }
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewProductFromCart")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewProductFromCart(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (empty($this->cart[$product])) {
                throw new Exception('Selected product is not in cart');
            }
        } else {
            // This transition need this guard: subject.cartHasProducts()
            $product = array_rand($this->cart);
            $data->set('product', $product);
        }
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewProductFromCategory")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function viewProductFromCategory(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (!in_array($product, $this->productsInCategory[$this->category])) {
                throw new Exception('Selected product is not in current category');
            }
        } else {
            // This transition need this guard: subject.categoryHasProducts()
            $products = $this->productsInCategory[$this->category] ?? [];
            $product = $products[array_rand($products)];
            $data->set('product', $product);
        }
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewCartFromHome")
     *
     * @param StepData $data
     */
    public function viewCartFromHome(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromCategory")
     *
     * @param StepData $data
     */
    public function viewCartFromCategory(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromProduct")
     *
     * @param StepData $data
     */
    public function viewCartFromProduct(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromCheckout")
     *
     * @param StepData $data
     */
    public function viewCartFromCheckout(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromHome")
     *
     * @param StepData $data
     */
    public function checkoutFromHome(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromCategory")
     *
     * @param StepData $data
     */
    public function checkoutFromCategory(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromProduct")
     *
     * @param StepData $data
     */
    public function checkoutFromProduct(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromCart")
     *
     * @param StepData $data
     */
    public function checkoutFromCart(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCategory")
     *
     * @param StepData $data
     */
    public function backToHomeFromCategory(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromProduct")
     *
     * @param StepData $data
     */
    public function backToHomeFromProduct(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCart")
     *
     * @param StepData $data
     */
    public function backToHomeFromCart(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCheckout")
     *
     * @param StepData $data
     */
    public function backToHomeFromCheckout(StepData $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("addFromHome")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function addFromHome(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (!in_array($product, $this->featuredProducts)) {
                throw new Exception('Selected product is not in featured products list');
            }
        } else {
            $product = $this->featuredProducts[array_rand($this->featuredProducts)];
            $data->set('product', $product);
        }
        if (!$this->testingModel) {
            if (in_array($product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        } else {
            ++$this->cart[$product];
        }
    }

    /**
     * @Transition("addFromCategory")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function addFromCategory(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (!in_array($product, $this->productsInCategory[$this->category])) {
                throw new Exception('Selected product is not in current category');
            }
        } else {
            // This transition need this guard: subject.categoryHasProducts()
            $products = $this->productsInCategory[$this->category] ?? [];
            $product = $products[array_rand($products)];
            $data->set('product', $product);
        }
        if (!$this->testingModel) {
            if (in_array($product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$product])) {
            $this->cart[$product] = 1;
        } else {
            ++$this->cart[$product];
        }
    }

    /**
     * @Transition("addFromProduct")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function addFromProduct(StepData $data)
    {
        if (!$this->testingModel) {
            if (in_array($this->product, $this->needOptions)) {
                throw new Exception('You need to specify options for this product! Can not add product');
            }
        }
        if (!isset($this->cart[$this->product])) {
            $this->cart[$this->product] = 1;
        } else {
            ++$this->cart[$this->product];
        }
    }

    /**
     * @Transition("remove")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function remove(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (empty($this->cart[$product])) {
                throw new Exception('Selected product is not in cart');
            }
        } else {
            // This transition need this guard: subject.cartHasProducts()
            $product = array_rand($this->cart);
            $data->set('product', $product);
        }
        unset($this->cart[$product]);
    }

    /**
     * @Transition("update")
     *
     * @param StepData $data
     *
     * @throws Exception
     */
    public function update(StepData $data)
    {
        if ($data->has('product')) {
            $product = $data->get('product');
            if (empty($this->cart[$product])) {
                throw new Exception('Selected product is not in cart');
            }
        } else {
            // This transition need this guard: subject.cartHasProducts()
            $product = array_rand($this->cart);
            $data->set('product', $product);
        }
        $this->cart[$product] = rand(1, 99);
    }

    /**
     * @Transition("useCoupon")
     */
    public function useCoupon()
    {
    }

    /**
     * @Transition("estimateShippingAndTaxes")
     */
    public function estimateShippingAndTaxes()
    {
    }

    /**
     * @Transition("useGiftCertificate")
     */
    public function useGiftCertificate()
    {
    }

    /**
     * @Place("home")
     */
    public function home()
    {
    }

    /**
     * @Place("category")
     */
    public function category()
    {
    }

    /**
     * @Place("product")
     */
    public function product()
    {
    }

    /**
     * @Place("cart")
     */
    public function cart()
    {
    }

    /**
     * @Place("checkout")
     *
     * @throws Exception
     */
    public function checkout()
    {
        if (!$this->testingModel) {
            foreach ($this->cart as $product => $quantity) {
                if (in_array($product, $this->outOfStock)) {
                    throw new Exception('You added an out-of-stock product into cart! Can not checkout');
                }
            }
        }
    }

    public function hasCoupon(): bool
    {
        return true;
    }

    public function hasGiftCertificate(): bool
    {
        return true;
    }

    public function cartHasProducts(): bool
    {
        return !empty($this->cart);
    }

    public function categoryHasProducts(): bool
    {
        $products = $this->productsInCategory[$this->category] ?? [];

        return !empty($products);
    }

    public function getScreenshotUrl($bugId, $index)
    {
        return sprintf('http://localhost/mbt-api/bug-screenshot/%d/%d', $bugId, $index);
    }
}
