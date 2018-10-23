<?php

namespace App\Subject;

use Exception;
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

    /**
     * @var int
     */
    protected $step = 1;

    /**
     * @var bool
     */
    protected $usingNewBillingAddress = false;

    /**
     * @var bool
     */
    protected $usingNewDeliveryAddress = false;

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

    public function useExistingBillingAddress()
    {
        $this->usingNewBillingAddress = false;
    }

    public function useNewBillingAddress()
    {
        $this->usingNewBillingAddress = true;
    }

    public function useExistingDeliveryAddress()
    {
        $this->usingNewDeliveryAddress = false;
    }

    public function useNewDeliveryAddress()
    {
        $this->usingNewDeliveryAddress = true;
    }

    public function step1()
    {
        $this->step = 1;
    }

    public function step2()
    {
        $this->step = 2;
    }

    public function step3()
    {
        $this->step = 3;
    }

    public function step4()
    {
        $this->step = 4;
    }

    public function step5()
    {
        $this->step = 5;
    }

    public function step6()
    {
        $this->step = 6;
    }

    /**
     * @throws Exception
     */
    public function goFromStep3ToStep4()
    {
        if (!$this->testing) {
            if ($this->step === 3 && $this->usingNewBillingAddress) {
                throw new Exception('Link to step 4 has been removed after using new billing address');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function goFromStep3ToStep5()
    {
        if (!$this->testing) {
            if ($this->step === 3 && $this->usingNewBillingAddress) {
                throw new Exception('Link to step 5 has been removed after using new billing address');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function goFromStep3ToStep6()
    {
        if (!$this->testing) {
            if ($this->step === 3 && $this->usingNewBillingAddress) {
                throw new Exception('Link to step 6 has been removed after using new billing address');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function goFromStep4ToStep5()
    {
        if (!$this->testing) {
            if ($this->step === 4 && $this->usingNewDeliveryAddress) {
                throw new Exception('Link to step 5 has been removed after using new delivery address');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function goFromStep4ToStep6()
    {
        if (!$this->testing) {
            if ($this->step === 4 && $this->usingNewDeliveryAddress) {
                throw new Exception('Link to step 6 has been removed after using new delivery address');
            }
        }
    }
}
