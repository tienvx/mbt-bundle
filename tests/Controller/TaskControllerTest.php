<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Router;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testStart()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mbt/start-task/1');

        $this->assertContains('Start!', $client->getResponse()->getContent());
    }

    public function testStop()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mbt/stop-task/1');

        $this->assertContains('Stop!', $client->getResponse()->getContent());
    }
}
