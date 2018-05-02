<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

use Tienvx\Bundle\MbtBundle\Tests\AbstractTestCase;

class AbstractApiTestCase extends AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->runCommand('doctrine:database:create');
        $this->runCommand('doctrine:schema:create');
        $this->runCommand('doctrine:fixtures:load --purge-with-truncate');
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
