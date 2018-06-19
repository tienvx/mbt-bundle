<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Api;

class ReproducePathTest extends ApiTestCase
{
    public function testGetReproducePaths()
    {
        $response = $this->makeApiRequest('GET', '/mbt/reproduce_paths');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        [
          {
            "id": 1,
            "task": "/mbt/tasks/1",
            "bugMessage": "Something happen on shopping_cart model",
            "steps": "step1 step2 step3",
            "length": 3
          },
          {
            "id": 2,
            "task": "/mbt/tasks/1",
            "bugMessage": "We found a bug on shopping_cart model",
            "steps": "step1 step2 step3 step4 step5",
            "length": 5
          },
          {
            "id": 3,
            "task": "/mbt/tasks/3",
            "bugMessage": "Weird bug when we test shoping_cart model",
            "steps": "step1 step2",
            "length": 2
          }
        ]', $response->getContent());
    }

    public function testCreateReproducePath()
    {
        $response = $this->makeApiRequest('POST', '/mbt/reproduce_paths', '
        {
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "steps": "step1 step2 step3.1 step3.2",
            "length": 4
        }');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertJsonStringEqualsJsonString('
        {
            "id": 4,
            "task": "/mbt/tasks/2",
            "bugMessage": "This bug never happen on task 2",
            "steps": "step1 step2 step3.1 step3.2",
            "length": 4
        }', $response->getContent());
    }

    public function testCreateInvalidReproducePath()
    {
        $response = $this->makeApiRequest('POST', '/mbt/reproduce_paths', '
        {
            "task": "/mbt/tasks/3",
            "bugMessage": "This bug is invalid",
            "steps": "How to reproduce this bug?",
            "length": "invalid-length"
        }');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertArraySubset(json_decode('
        {
            "title": "An error occurred",
            "detail": "The type of the \"length\" attribute must be \"int\", \"string\" given."
        }', true), json_decode($response->getContent(), true));
    }
}
