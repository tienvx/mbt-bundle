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
                "reducer": "loop",
                "reporter": "email",
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
                "model": "checkout",
                "generator": "random",
                "reducer": "binary",
                "reporter": "email",
                "progress": 64,
                "status": "in-progress",
                "bugs": [
                    "/mbt/bugs/3"
                ]
            },
            {
                "id": 3,
                "title": "Task 3",
                "model": "shopping_cart",
                "generator": "random",
                "reducer": "greedy",
                "reporter": "email",
                "progress": 100,
                "status": "completed",
                "bugs": []
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
            "detail": "generator: \"invalid-generator\" is not a valid generator.\nreducer: \"invalid-reducer\" is not a valid path reducer.\nreporter: \"invalid-reporter\" is not a valid reporter.\nprogress: This value should be 100 or less.\nstatus: The value you selected is not a valid choice.",
            "violations": [
                {
                    "propertyPath": "generator",
                    "message": "\"invalid-generator\" is not a valid generator."
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
