<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Annotation\Transition;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class Checkout extends AbstractSubject
{
    /**
     * @var int
     */
    protected $productId = 47;

    /**
     * @var bool
     */
    protected $loggedIn = false;

    /**
     * @var bool
     */
    protected $guestCheckout = false;

    /**
     * @var bool
     */
    protected $registerAccount = false;

    public static function getName(): string
    {
        return 'checkout';
    }

    public function loggedIn()
    {
        return $this->loggedIn;
    }

    public function doingGuestCheckout()
    {
        return $this->guestCheckout;
    }

    public function doingRegisterAccount()
    {
        return $this->registerAccount;
    }

    /**
     * @Transition("login")
     */
    public function login()
    {
        $this->loggedIn = true;
    }

    /**
     * @Transition("guestCheckout")
     */
    public function guestCheckout()
    {
        $this->guestCheckout = true;
    }

    /**
     * @Transition("registerAccount")
     */
    public function registerAccount()
    {
        $this->registerAccount = true;
    }

    /**
     * @Transition("guestCheckoutAndAddBillingAddress")
     */
    public function guestCheckoutAndAddBillingAddress()
    {
        $this->guestCheckout = false;
    }

    /**
     * @Transition("registerAndAddBillingAddress")
     *
     * @throws Exception
     */
    public function registerAndAddBillingAddress()
    {
        $this->registerAccount = false;
        $this->loggedIn = true;
        throw new Exception('Still able to do register account, guest checkout or login when logged in!');
    }

    public function hasExistingBillingAddress(): bool
    {
        return true;
    }

    public function hasExistingDeliveryAddress(): bool
    {
        return true;
    }

    public function getScreenshotUrl($bugId, $index)
    {
        return sprintf('http://localhost/mbt-api/bug-screenshot/%d/%d', $bugId, $index);
    }
}
