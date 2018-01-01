<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class AbstractApiTestCase extends WebTestCase
{
    protected static $application;
    protected static $client;

    protected function setUp()
    {
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('doctrine:fixtures:load --purge-with-truncate');
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            self::$client = static::createClient();

            self::$application = new Application(self::$client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }
}
