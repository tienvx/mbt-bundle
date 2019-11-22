<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class TokenHelper
{
    /**
     * @var TokenStorage|null
     */
    private $tokenStorage = null;

    public function setTokenStorage(TokenStorage $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setAnonymousToken(): void
    {
        if ($this->tokenStorage) {
            $token = new AnonymousToken('default', 'anon.');
            $this->tokenStorage->setToken($token);
        }
    }
}
