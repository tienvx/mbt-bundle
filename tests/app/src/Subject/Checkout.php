<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class Checkout extends Subject
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

    public function login()
    {
        $this->loggedIn = true;
    }

    public function guestCheckout()
    {
        $this->guestCheckout = true;
    }

    public function registerAccount()
    {
        $this->registerAccount = true;
    }

    public function guestCheckoutAndAddBillingAddress()
    {
        $this->guestCheckout = false;
    }

    /**
     * @throws Exception
     */
    public function registerAndAddBillingAddress()
    {
        $this->registerAccount = false;
        $this->loggedIn = true;
        if (!$this->testing) {
            throw new Exception('Still able to do register account, guest checkout or login when logged in!');
        }
    }
}
