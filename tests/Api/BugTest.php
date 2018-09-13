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
                "path": "step1 step2 step3",
                "length": 3,
                "task": "/mbt/tasks/1",
                "bugMessage": "Something happen on shopping_cart model"
            },
            {
                "id": 2,
                "title": "Bug 2",
                "status": "valid",
                "path": "step1 step2 step3 step4 step5",
                "length": 5,
                "task": "/mbt/tasks/1",
                "bugMessage": "We found a bug on shopping_cart model"
            },
            {
                "id": 3,
                "title": "Bug 3",
                "status": "valid",
                "path": "step1 step2",
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
            "path": "step1 step2 step3.1 step3.2",
            "length": 4,
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
            "path": "step1 step2 step3.1 step3.2",
            "length": 4,
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
            "path": "How to reproduce this bug?",
            "length": 5
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
            "title": "An error occurred",
            "detail": "status: The value you selected is not a valid choice.",
            "violations": [
                {
                    "propertyPath": "status",
                    "message": "The value you selected is not a valid choice."
                }
            ]
        }', true), json_decode($response->getContent(), true));
    }
}
