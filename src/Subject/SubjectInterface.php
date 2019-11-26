<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

interface SubjectInterface
{
    public function setUp(bool $testing = false): void;

    public function tearDown(): void;
}
