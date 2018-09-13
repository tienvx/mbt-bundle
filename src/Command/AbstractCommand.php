<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

abstract class AbstractCommand extends Command
{
    /**
     * @var TokenStorage|null
     */
    private $tokenStorage = null;

    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    protected function setAnonymousToken()
    {
        if ($this->tokenStorage) {
            $token = new AnonymousToken('default', 'anon.');
            $this->tokenStorage->setToken($token);
        }
    }
}
