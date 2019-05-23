<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;

abstract class TestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    protected function setUp()
    {
        $this->application = $this->getApplication();
    }

    /**
     * @param $command
     *
     * @return int
     *
     * @throws Exception
     */
    protected function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return $this->application->run(new StringInput($command));
    }

    protected function getApplication()
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }
}
