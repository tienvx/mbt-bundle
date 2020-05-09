<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

trait SetUpTearDownTrait
{
    public function setUp(bool $trying = false): void
    {
        // Init system-under-test connection e.g.
        // $this->client = Client::createChromeClient();
    }

    public function tearDown(): void
    {
        // Destroy system-under-test connection e.g.
        // $this->client->quit();
    }
}
