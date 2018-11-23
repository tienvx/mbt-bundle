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
    protected $registered = false;

    /**
     * @throws Exception
     */
    public function login()
    {
        if (!$this->testing) {
            if (!$this->loggedIn && $this->registered) {
                throw new Exception('Should login automatically after registering');
            }
        }
        $this->loggedIn = true;
    }

    public function loggedIn()
    {
        return $this->loggedIn;
    }

    public function registerAccount()
    {
        $this->registered = true;
    }
}
