<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class BugTest extends AbstractApiTestCase
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
            "task": "/mbt/tasks/1",
            "title": "Bug 1",
            "message": "Something happen on shopping_cart model",
            "steps": "step1 step2 step3",
            "status": "unverified",
            "reporter": "email"
          },
          {
            "id": 2,
            "task": "/mbt/tasks/1",
            "title": "Bug 2",
            "message": "Something happen on shopping_cart model",
            "steps": "step1 step2 step3 step4 step5",
            "status": "valid",
            "reporter": "email"
          },
          {
            "id": 3,
            "task": "/mbt/tasks/3",
            "title": "Bug 3",
            "message": "Something happen on shopping_cart model",
            "steps": "step1 step2",
            "status": "invalid",
            "reporter": "email"
          }
        ]', $response->getContent());
    }

    public function testCreateBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/2",
            "title": "Bug 4",
            "message": "This bug never happen on task 2",
            "steps": "step1 step2 step3.1 step3.2",
            "status": "unverified",
            "reporter": "email"
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
          "id": 4,
          "task": "/mbt/tasks/2",
          "title": "Bug 4",
          "message": "This bug never happen on task 2",
          "steps": "step1 step2 step3.1 step3.2",
          "status": "unverified",
          "reporter": "email"
        }', $response->getContent());
    }

    public function testCreateInvalidBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/3",
            "title": "Bug 5",
            "message": "This bug is invalid",
            "steps": "How to reproduce this bug?",
            "status": "invalid-bug",
            "reporter": "invalid-reporter"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
          "title": "An error occurred",
          "detail": "status: The value you selected is not a valid choice.\nreporter: \"invalid-reporter\" is not a valid reporter.",
          "violations": [
            {
              "propertyPath": "status",
              "message": "The value you selected is not a valid choice."
            },
            {
              "propertyPath": "reporter",
              "message": "\"invalid-reporter\" is not a valid reporter."
            }
          ]
        }', true), json_decode($response->getContent(), true));
    }
}
