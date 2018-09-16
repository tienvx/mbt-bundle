<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class BugTest extends ApiTestCase
{
    public function testGetBugs()
    {
        $response = $this->makeApiRequest('GET', '/mbt/bugs');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        [
            {
                "id": 1,
                "title": "Bug 1",
                "status": "unverified",
                "path": "C:34:\"Tienvx\\\\Bundle\\\\MbtBundle\\\\Graph\\\\Path\":165:{a:3:{i:0;a:3:{i:0;N;i:1;s:11:\"transition1\";i:2;s:11:\"transition2\";}i:1;a:3:{i:0;N;i:1;a:0:{}i:2;a:0:{}}i:2;a:3:{i:0;s:6:\"place1\";i:1;s:6:\"place2\";i:2;s:6:\"place3\";}}}",
                "length": 3,
                "task": "/mbt/tasks/1",
                "bugMessage": "Something happen on shopping_cart model"
            },
            {
                "id": 2,
                "title": "Bug 2",
                "status": "valid",
                "path": "C:34:\"Tienvx\\\\Bundle\\\\MbtBundle\\\\Graph\\\\Path\":265:{a:3:{i:0;a:5:{i:0;N;i:1;s:11:\"transition1\";i:2;s:11:\"transition2\";i:3;s:11:\"transition3\";i:4;s:11:\"transition4\";}i:1;a:5:{i:0;N;i:1;a:0:{}i:2;a:0:{}i:3;a:0:{}i:4;a:0:{}}i:2;a:5:{i:0;s:6:\"place1\";i:1;s:6:\"place2\";i:2;s:6:\"place3\";i:3;s:6:\"place4\";i:4;s:6:\"place5\";}}}",
                "length": 5,
                "task": "/mbt/tasks/1",
                "bugMessage": "We found a bug on shopping_cart model"
            },
            {
                "id": 3,
                "title": "Bug 3",
                "status": "valid",
                "path": "C:34:\"Tienvx\\\\Bundle\\\\MbtBundle\\\\Graph\\\\Path\":115:{a:3:{i:0;a:2:{i:0;N;i:1;s:11:\"transition1\";}i:1;a:2:{i:0;N;i:1;a:0:{}}i:2;a:2:{i:0;s:6:\"place1\";i:1;s:6:\"place2\";}}}",
                "length": 2,
                "task": "/mbt/tasks/2",
                "bugMessage": "Weird bug when we test shoping_cart model"
             }
        ]', $response->getContent());
    }

    public function testCreateBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "path": "C:34:\"Tienvx\\\\Bundle\\\\MbtBundle\\\\Graph\\\\Path\":196:{a:3:{i:0;a:3:{i:0;N;i:1;s:11:\"transition1\";i:2;s:11:\"transition2\";}i:1;a:3:{i:0;N;i:1;a:0:{}i:2;a:0:{}}i:2;a:3:{i:0;s:6:\"place1\";i:1;s:6:\"place2\";i:2;a:2:{i:0;s:8:\"place3.1\";i:1;s:8:\"place3.2\";}}}}",
            "length": 3,
            "title": "Bug 3",
            "status": "unverified"
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
            "id": 4,
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "path": "C:34:\"Tienvx\\\\Bundle\\\\MbtBundle\\\\Graph\\\\Path\":196:{a:3:{i:0;a:3:{i:0;N;i:1;s:11:\"transition1\";i:2;s:11:\"transition2\";}i:1;a:3:{i:0;N;i:1;a:0:{}i:2;a:0:{}}i:2;a:3:{i:0;s:6:\"place1\";i:1;s:6:\"place2\";i:2;a:2:{i:0;s:8:\"place3.1\";i:1;s:8:\"place3.2\";}}}}",
            "length": 3,
            "title": "Bug 3",
            "status": "unverified"
        }', $response->getContent());
    }

    public function testCreateInvalidBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/3",
            "title": "Bug 5",
            "status": "invalid-bug",
            "bugMessage": "This bug is invalid",
            "path": "The way to reproduce this bug",
            "length": 5
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
            "title":"An error occurred",
            "detail":"status: The value you selected is not a valid choice.\npath: \u0022The way to reproduce this bug\u0022 is not a valid path.",
            "violations":[
                {
                    "propertyPath":"status",
                    "message":"The value you selected is not a valid choice."
                },
                {
                    "propertyPath":"path",
                    "message":"\u0022The way to reproduce this bug\u0022 is not a valid path."
                }
            ]
        }', true), json_decode($response->getContent(), true));
    }
}
