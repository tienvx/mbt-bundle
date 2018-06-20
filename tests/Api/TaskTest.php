<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class TaskTest extends ApiTestCase
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
            "stopCondition": "max-length",
            "stopConditionArguments": "{\"a\":\"b\"}",
            "reducer": "loop",
            "reporter": "email",
            "progress": 0,
            "status": "not-started",
            "reproducePaths": [
                "/mbt/reproduce_paths/1",
                "/mbt/reproduce_paths/2"
            ]
          },
          {
            "id": 2,
            "title": "Task 2",
            "model": "shopping_cart",
            "generator": "random",
            "stopCondition": "max-length",
            "stopConditionArguments": "{\"a\":\"b\"}",
            "reducer": "binary",
            "reporter": "email",
            "progress": 64,
            "status": "in-progress",
            "reproducePaths": []
          },
          {
            "id": 3,
            "title": "Task 3",
            "model": "shopping_cart",
            "generator": "random",
            "stopCondition": "max-length",
            "stopConditionArguments": "{\"a\":\"b\"}",
            "reducer": "greedy",
            "reporter": "email",
            "progress": 100,
            "status": "completed",
            "reproducePaths": [
                "/mbt/reproduce_paths/3"
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
          "reporter": "email",
          "stopCondition": "max-length",
          "stopConditionArguments": "{\"a\":\"b\"}",
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
          "reporter": "email",
          "stopCondition": "max-length",
          "stopConditionArguments": "{\"a\":\"b\"}",
          "reducer": "weighted-random",
          "progress": 0,
          "status": "not-started",
          "reproducePaths": []
        }', $response->getContent());
    }

    public function testCreateInvalidTask()
    {
        $response = $this->makeApiRequest('POST', '/mbt/tasks', '
        {
            "title": "Test shopping cart",
            "model": "shopping_cart",
            "generator": "invalid-generator",
            "stopCondition": "invalid-stop-condition",
            "stopConditionArguments": "not a json string",
            "reducer": "invalid-reducer",
            "reporter": "invalid-reporter",
            "progress": 111,
            "status": "not-supported"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
            "title": "An error occurred",
            "detail": "generator: \"invalid-generator\" is not a valid generator.\nstopCondition: \"invalid-stop-condition\" is not a valid stop condition.\nstopConditionArguments: \"\"not a json string\"\" is not a valid json string.\nreducer: \"invalid-reducer\" is not a valid path reducer.\nreporter: \"invalid-reporter\" is not a valid reporter.\nprogress: This value should be 100 or less.\nstatus: The value you selected is not a valid choice.",
            "violations": [
                {
                    "propertyPath": "generator",
                    "message": "\"invalid-generator\" is not a valid generator."
                },
                {
                    "propertyPath": "stopCondition",
                    "message": "\"invalid-stop-condition\" is not a valid stop condition."
                },
                {
                    "propertyPath": "stopConditionArguments",
                    "message": "\"\"not a json string\"\" is not a valid json string."
                },
                {
                    "propertyPath": "reducer",
                    "message": "\"invalid-reducer\" is not a valid path reducer."
                },
                {
                    "propertyPath": "reporter",
                    "message": "\"invalid-reporter\" is not a valid reporter."
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
        }', true), json_decode($response->getContent(), true));
    }
}
