<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Fixtures\Exception;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class HttpClientException extends \Exception implements ExceptionInterface
{
}
