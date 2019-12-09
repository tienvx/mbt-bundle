<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenHelper
{
    /**
     * @var TokenStorageInterface|null
     */
    private $tokenStorage = null;

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setAnonymousToken(): void
    {
        if ($this->tokenStorage instanceof TokenStorageInterface) {
            $token = new AnonymousToken('default', 'anon.');
            $this->tokenStorage->setToken($token);
        }
    }
}
