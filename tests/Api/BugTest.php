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
            "paths": [
              "step 1",
              "step 2",
              "step 3"
            ],
            "status": "unverified"
          },
          {
            "id": 2,
            "task": "/mbt/tasks/1",
            "title": "Bug 2",
            "message": "Something happen on shopping_cart model",
            "paths": [
              "step 1",
              "step 2",
              "step 3",
              "step 4",
              "step 5"
            ],
            "status": "valid"
          },
          {
            "id": 3,
            "task": "/mbt/tasks/3",
            "title": "Bug 3",
            "message": "Something happen on shopping_cart model",
            "paths": [
              "step 1",
              "step 2"
            ],
            "status": "invalid"
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
            "paths": [
              "step 1",
              "step 2",
              "step 3.1",
              "step 3.2"
            ],
            "status": "unverified"
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
          "id": 4,
          "task": "/mbt/tasks/2",
          "title": "Bug 4",
          "message": "This bug never happen on task 2",
          "paths": [
            "step 1",
            "step 2",
            "step 3.1",
            "step 3.2"
          ],
          "status": "unverified"
        }', $response->getContent());
    }

    public function testCreateInvalidBug()
    {
        $response = $this->makeApiRequest('POST', '/mbt/bugs', '
        {
            "task": "/mbt/tasks/3",
            "title": "Bug 5",
            "message": "This bug is invalid",
            "paths": [
              "How to reproduce this bug?"
            ],
            "status": "invalid-bug"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
          "type": "https://tools.ietf.org/html/rfc2616#section-10",
          "title": "An error occurred",
          "detail": "status: The value you selected is not a valid choice.",
          "violations": [
            {
              "propertyPath": "status",
              "message": "The value you selected is not a valid choice."
            }
          ]
        }', $response->getContent());
    }
}
