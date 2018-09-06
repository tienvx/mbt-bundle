<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Subject;

use Tienvx\Bundle\MbtBundle\Subject\Subject;

class Checkout extends Subject
{
    /**
     * @var bool
     */
    protected $loggedIn = false;

    /**
     * @var bool
     */
    protected $doingGuestCheckout = false;

    /**
     * @var bool
     */
    protected $registeringAccount = false;

    /**
     * @var bool
     */
    protected $accountAdded = false;

    /**
     * @var bool
     */
    protected $billingDetailsAdded = false;

    /**
     * @var bool
     */
    protected $deliveryDetailsAdded = false;

    /**
     * @var bool
     */
    protected $deliveryMethodAdded = false;

    /**
     * @var bool
     */
    protected $paymentMethodAdded = false;

    public function login()
    {
        $this->loggedIn = true;
        $this->doingGuestCheckout = false;
        $this->registeringAccount = false;
    }

    public function loggedIn()
    {
        return $this->loggedIn;
    }

    public function guestCheckout()
    {
        $this->doingGuestCheckout = true;
    }

    public function doingGuestCheckout()
    {
        return $this->doingGuestCheckout;
    }

    public function registerAccount()
    {
        $this->registeringAccount = true;
    }

    public function registeringAccount()
    {
        return $this->registeringAccount;
    }

    public function isAccountAdded()
    {
        return $this->accountAdded;
    }

    public function isBillingDetailsAdded()
    {
        return $this->billingDetailsAdded;
    }

    public function isDeliveryDetailsAdded()
    {
        return $this->deliveryDetailsAdded;
    }

    public function isDeliveryMethodAdded()
    {
        return $this->deliveryMethodAdded;
    }

    public function isPaymentMethodAdded()
    {
        return $this->paymentMethodAdded;
    }

    public function accountAdded()
    {
        $this->accountAdded = true;
    }

    public function billingDetailsAdded()
    {
        $this->billingDetailsAdded = true;
    }

    public function deliveryDetailsAdded()
    {
        $this->deliveryDetailsAdded = true;
    }

    public function deliveryMethodAdded()
    {
        $this->deliveryMethodAdded = true;
    }

    public function paymentMethodAdded()
    {
        $this->paymentMethodAdded = true;
    }
}
