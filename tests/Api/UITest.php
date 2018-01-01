<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UITest extends WebTestCase
{
    public function testGet()
    {
        $client = static::createClient();

        $client->request('GET', '/mbt');
        $this->assertContains('swagger-ui', $client->getResponse()->getContent());
        $this->assertContains('/mbt/index.jsonld', $client->getResponse()->getContent());
        $this->assertContains('/mbt/index.json', $client->getResponse()->getContent());
        $this->assertContains('/mbt/index.html', $client->getResponse()->getContent());

        $client->request('GET', '/mbt/tasks');
        $this->assertContains('swagger-ui', $client->getResponse()->getContent());
        $this->assertContains('/mbt/tasks.jsonld', $client->getResponse()->getContent());
        $this->assertContains('/mbt/tasks.json', $client->getResponse()->getContent());
        $this->assertContains('/mbt/tasks.html', $client->getResponse()->getContent());

        $client->request('GET', '/mbt/bugs');
        $this->assertContains('swagger-ui', $client->getResponse()->getContent());
        $this->assertContains('/mbt/bugs.jsonld', $client->getResponse()->getContent());
        $this->assertContains('/mbt/bugs.json', $client->getResponse()->getContent());
        $this->assertContains('/mbt/bugs.html', $client->getResponse()->getContent());
    }
}
