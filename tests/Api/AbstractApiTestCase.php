<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class AbstractApiTestCase extends WebTestCase
{
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');
        $this->runCommand('doctrine:fixtures:load --purge-with-truncate');
    }

    protected function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return $this->getApplication()->run(new StringInput($command));
    }

    protected function getApplication()
    {
        if (null === $this->application) {
            $this->application = new Application($this->client->getKernel());
            $this->application->setAutoExit(false);
        }

        return $this->application;
    }

    /**
     * Makes a request to our API
     *
     * @param string $method
     * @param string $url
     * @param string $jsonPayload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function makeApiRequest($method, $url, $jsonPayload = null)
    {
        $headers = [
            'HTTP_ACCEPT'  => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];

        $this->client->request($method, $url, [], [], $headers, $jsonPayload);

        return $this->client->getResponse();
    }
}
