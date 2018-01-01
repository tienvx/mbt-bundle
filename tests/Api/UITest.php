<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class UITest extends AbstractApiTestCase
{
    public function testGet()
    {
        $this->client->request('GET', '/mbt');
        $response = $this->client->getResponse()->getContent();
        $this->assertContains('swagger-ui', $response);
        $this->assertContains('/mbt/index.jsonld', $response);
        $this->assertContains('/mbt/index.json', $response);
        $this->assertContains('/mbt/index.html', $response);

        $this->client->request('GET', '/mbt/tasks');
        $response = $this->client->getResponse()->getContent();
        $this->assertContains('swagger-ui', $response);
        $this->assertContains('/mbt/tasks.jsonld', $response);
        $this->assertContains('/mbt/tasks.json', $response);
        $this->assertContains('/mbt/tasks.html', $response);

        $this->client->request('GET', '/mbt/bugs');
        $response = $this->client->getResponse()->getContent();
        $this->assertContains('swagger-ui', $response);
        $this->assertContains('/mbt/bugs.jsonld', $response);
        $this->assertContains('/mbt/bugs.json', $response);
        $this->assertContains('/mbt/bugs.html', $response);
    }
}
