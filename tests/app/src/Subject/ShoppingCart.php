<?php

namespace App\Subject;

use App\Helper\DataHelper;
use Exception;
use Tienvx\Bundle\MbtBundle\Annotation\Place;
use Tienvx\Bundle\MbtBundle\Annotation\Subject;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

/**
 * @Subject("shopping_cart")
 */
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

    /**
     * @Transition("viewAnyCategoryFromHome")
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromHome(Data $data)
    {
        $category = DataHelper::get($data, 'category', [$this, 'randomCategory'], [$this, 'validateCategory']);
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewOtherCategory")
     *
     * @throws Exception
     */
    public function viewOtherCategory(Data $data)
    {
        $category = DataHelper::get($data, 'category', [$this, 'randomCategory'], [$this, 'validateCategory']);
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewAnyCategoryFromProduct")
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromProduct(Data $data)
    {
        $category = DataHelper::get($data, 'category', [$this, 'randomCategory'], [$this, 'validateCategory']);
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewAnyCategoryFromCart")
     *
     * @throws Exception
     */
    public function viewAnyCategoryFromCart(Data $data)
    {
        $category = DataHelper::get($data, 'category', [$this, 'randomCategory'], [$this, 'validateCategory']);
        $this->category = $category;
        $this->product = null;
    }

    /**
     * @Transition("viewProductFromHome")
     *
     * @throws Exception
     */
    public function viewProductFromHome(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromHome'], [$this, 'validateProductFromHome']);
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewProductFromCart")
     *
     * @throws Exception
     */
    public function viewProductFromCart(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromCart'], [$this, 'validateProductFromCart']);
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewProductFromCategory")
     *
     * @throws Exception
     */
    public function viewProductFromCategory(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromCategory'], [$this, 'validateProductFromCategory']);
        $this->product = $product;
        $this->category = null;
    }

    /**
     * @Transition("viewCartFromHome")
     */
    public function viewCartFromHome(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromCategory")
     */
    public function viewCartFromCategory(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromProduct")
     */
    public function viewCartFromProduct(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("viewCartFromCheckout")
     */
    public function viewCartFromCheckout(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromHome")
     */
    public function checkoutFromHome(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromCategory")
     */
    public function checkoutFromCategory(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromProduct")
     */
    public function checkoutFromProduct(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("checkoutFromCart")
     */
    public function checkoutFromCart(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCategory")
     */
    public function backToHomeFromCategory(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromProduct")
     */
    public function backToHomeFromProduct(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCart")
     */
    public function backToHomeFromCart(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("backToHomeFromCheckout")
     */
    public function backToHomeFromCheckout(Data $data)
    {
        $this->category = null;
        $this->product = null;
    }

    /**
     * @Transition("addFromHome")
     *
     * @throws Exception
     */
    public function addFromHome(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromHome'], [$this, 'validateProductFromHome']);
        if (in_array($product, $this->needOptions)) {
            throw new Exception('You need to specify options for this product! Can not add product');
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
     * @throws Exception
     */
    public function addFromCategory(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromCategory'], [$this, 'validateProductFromCategory']);
        if (in_array($product, $this->needOptions)) {
            throw new Exception('You need to specify options for this product! Can not add product');
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
     * @throws Exception
     */
    public function addFromProduct(Data $data)
    {
        if (in_array($this->product, $this->needOptions)) {
            throw new Exception('You need to specify options for this product! Can not add product');
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
     * @throws Exception
     */
    public function remove(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromCart'], [$this, 'validateProductFromCart']);
        unset($this->cart[$product]);
    }

    /**
     * @Transition("update")
     *
     * @throws Exception
     */
    public function update(Data $data)
    {
        $product = DataHelper::get($data, 'product', [$this, 'randomProductFromCart'], [$this, 'validateProductFromCart']);
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
        foreach ($this->cart as $product => $quantity) {
            if (in_array($product, $this->outOfStock)) {
                throw new Exception('You added an out-of-stock product into cart! Can not checkout');
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

    public function randomCategory()
    {
        return $this->categories[array_rand($this->categories)];
    }

    /**
     * @param $category
     *
     * @throws Exception
     */
    public function validateCategory($category)
    {
        if (!in_array($category, $this->categories)) {
            throw new Exception('Selected category is invalid');
        }
    }

    public function randomProductFromHome()
    {
        return $this->featuredProducts[array_rand($this->featuredProducts)];
    }

    /**
     * @param $product
     *
     * @throws Exception
     */
    public function validateProductFromHome($product)
    {
        if (!in_array($product, $this->featuredProducts)) {
            throw new Exception('Selected product is not in featured products list');
        }
    }

    public function randomProductFromCart()
    {
        // This transition need this guard: subject.cartHasProducts()
        return array_rand($this->cart);
    }

    /**
     * @param $product
     *
     * @throws Exception
     */
    public function validateProductFromCart($product)
    {
        if (empty($this->cart[$product])) {
            throw new Exception('Selected product is not in cart');
        }
    }

    public function randomProductFromCategory()
    {
        // This transition need this guard: subject.categoryHasProducts()
        $products = $this->productsInCategory[$this->category] ?? [];

        return $products[array_rand($products)];
    }

    /**
     * @param $product
     *
     * @throws Exception
     */
    public function validateProductFromCategory($product)
    {
        if (!in_array($product, $this->productsInCategory[$this->category])) {
            throw new Exception('Selected product is not in current category');
        }
    }
}
