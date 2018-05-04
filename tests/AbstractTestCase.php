<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class AbstractTestCase extends WebTestCase
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
        $this->client = $this->getClient();
        $this->application = $this->getApplication();
    }

    /**
     * @param $command
     * @return int
     * @throws \Exception
     */
    protected function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return $this->application->run(new StringInput($command));
    }

    protected function getApplication()
    {
        $application = new Application($this->client->getKernel());
        $application->setAutoExit(false);
        return $application;
    }

    protected function getClient()
    {
        $client = static::createClient();
        $client->disableReboot();
        return $client;
    }
}
