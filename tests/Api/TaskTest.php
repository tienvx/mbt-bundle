<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class TaskTest extends AbstractApiTestCase
{
    public function testGetTasks()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/mbt/tasks',
            [],
            [],
            [
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertContains('abc', $client->getResponse()->getContent());
    }

    public function testCreateTasks()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/mbt/tasks',
            [],
            [],
            [
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ],
            '
            {
              "title": "Test shopping cart",
              "model": "shopping_cart",
              "algorithm": "random",
              "progress": 0,
              "status": "not-started"
            }'
        );

        $this->assertContains('abc', $client->getResponse()->getContent());
    }
}
