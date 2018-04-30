<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class TaskTest extends AbstractApiTestCase
{
    public function testGetTasks()
    {
        $response = $this->makeApiRequest('GET', '/mbt/tasks');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        [
          {
            "id": 1,
            "title": "Task 1",
            "model": "shopping_cart",
            "generator": "random",
            "arguments": "{\"a\":\"b\"}",
            "reducer": "loop",
            "progress": 0,
            "status": "not-started",
            "bugs": [
              "/mbt/bugs/1",
              "/mbt/bugs/2"
            ]
          },
          {
            "id": 2,
            "title": "Task 2",
            "model": "shopping_cart",
            "generator": "random",
            "arguments": "{\"a\":\"b\"}",
            "reducer": "binary",
            "progress": 64,
            "status": "in-progress",
            "bugs": []
          },
          {
            "id": 3,
            "title": "Task 3",
            "model": "shopping_cart",
            "generator": "random",
            "arguments": "{\"a\":\"b\"}",
            "reducer": "greedy",
            "progress": 100,
            "status": "completed",
            "bugs": [
              "/mbt/bugs/3"
            ]
          }
        ]', $response->getContent());
    }

    public function testCreateTask()
    {
        $response = $this->makeApiRequest('POST', '/mbt/tasks', '
        {
          "title": "Test shopping cart",
          "model": "shopping_cart",
          "generator": "random",
          "arguments": "{\"a\":\"b\"}",
          "reducer": "weighted-random",
          "progress": 0,
          "status": "not-started"
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
          "id": 4,
          "title": "Test shopping cart",
          "model": "shopping_cart",
          "generator": "random",
          "arguments": "{\"a\":\"b\"}",
          "reducer": "weighted-random",
          "progress": 0,
          "status": "not-started",
          "bugs": []
        }', $response->getContent());
    }

    public function testCreateInvalidTask()
    {
        $response = $this->makeApiRequest('POST', '/mbt/tasks', '
        {
          "title": "Test shopping cart",
          "model": "shopping_cart",
          "generator": "invalid-generator",
          "arguments": "not a json string",
          "reducer": "invalid-reducer",
          "progress": 111,
          "status": "not-supported"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
          "type": "https://tools.ietf.org/html/rfc2616#section-10",
          "title": "An error occurred",
          "detail": "generator: \"invalid-generator\" is not a valid generator.\narguments: \"\"not a json string\"\" is not a valid json string.\nreducer: \"invalid-reducer\" is not a valid path reducer.\nprogress: This value should be 100 or less.\nstatus: The value you selected is not a valid choice.",
          "violations": [
            {
              "propertyPath": "generator",
              "message": "\"invalid-generator\" is not a valid generator."
            },
            {
                "propertyPath": "arguments",
                "message": "\"\"not a json string\"\" is not a valid json string."
            },
            {
                "propertyPath": "reducer",
                "message": "\"invalid-reducer\" is not a valid path reducer."
            },
            {
              "propertyPath": "progress",
              "message": "This value should be 100 or less."
            },
            {
              "propertyPath": "status",
              "message": "The value you selected is not a valid choice."
            }
          ]
        }', $response->getContent());
    }
}
