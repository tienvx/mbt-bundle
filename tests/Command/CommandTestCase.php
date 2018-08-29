<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tienvx\Bundle\MbtBundle\Tests\TestCase;

abstract class CommandTestCase extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        /** @var TokenStorage $tokenStorage */
        $tokenStorage = self::$container->get('security.token_storage');
        $token = new UsernamePasswordToken('test', 'test', 'main', array('ROLE_ADMIN'));
        $tokenStorage->setToken($token);
    }
}
