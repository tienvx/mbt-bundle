<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Client extends GuzzleClient
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->cacheDir = $this->params->get('kernel.cache_dir');
        parent::__construct();
    }

    public function __call($method, $args)
    {
        if ($method === 'post' && $args[0] === 'https://api.hipchat.com/v2/room/test.ModelBasedTesting/notification?auth_token=fake7f549278d7eafd9bd0ee637e5641399406b6') {
            exec("mkdir -p {$this->cacheDir}/hipchat");
            file_put_contents("{$this->cacheDir}/hipchat/message.data", $args[1]['json']['message']);
        }
        if ($method === 'post' && $args[0] === 'https://slack.com/api/chat.postMessage') {
            exec("mkdir -p {$this->cacheDir}/slack");
            file_put_contents("{$this->cacheDir}/slack/text.data", $args[1]['json']['text']);
        }
        if ($method === 'post' && $args[0] === 'https://api.github.com/repos/test/mbt/issues') {
            exec("mkdir -p {$this->cacheDir}/github");
            file_put_contents("{$this->cacheDir}/github/body.data", $args[1]['json']['body']);
        }
    }
}
