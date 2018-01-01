<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class TaskTest extends AbstractApiTestCase
{
    public function testGetTasks()
    {
        $response = $this->makeApiRequest('GET', '/mbt/tasks');
        $this->assertEquals('[{"id":1,"title":"Task 1","model":"shopping_cart","algorithm":"random","progress":0,"status":"not-started","bugs":["\/mbt\/bugs\/1","\/mbt\/bugs\/2"]},{"id":2,"title":"Task 2","model":"shopping_cart","algorithm":"random","progress":64,"status":"in-progress","bugs":[]},{"id":3,"title":"Task 3","model":"shopping_cart","algorithm":"random","progress":100,"status":"completed","bugs":["\/mbt\/bugs\/3"]}]', $response->getContent());
    }

    public function testCreateTasks()
    {
        $response = $this->makeApiRequest('POST', '/mbt/tasks', '
        {
          "title": "Test shopping cart",
          "model": "shopping_cart",
          "algorithm": "random",
          "progress": 0,
          "status": "not-started"
        }');

        $this->assertEquals('{"id":4,"title":"Test shopping cart","model":"shopping_cart","algorithm":"random","progress":0,"status":"not-started","bugs":[]}', $response->getContent());
    }
}
