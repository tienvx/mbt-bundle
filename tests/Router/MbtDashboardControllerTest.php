<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Router;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mbt');

        $this->assertContains('An Error Occurred: Internal Server Error', $client->getResponse()->getContent());
    }
}
