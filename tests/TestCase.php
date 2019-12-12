<?php

namespace Tienvx\Bundle\MbtBundle\Tests;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class TestCase extends KernelTestCase
{
    /**
     * @var Application
     */
    protected $application;

    protected function setUp(): void
    {
        $this->application = $this->getApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->application);
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
        return $this->application->run(new ArrayInput($command), new NullOutput());
    }

    protected function getApplication()
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }
}
