<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\FlysystemBundle\FlysystemBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Tienvx\Bundle\MbtBundle\TienvxMbtBundle;

return [
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    MakerBundle::class => ['all' => true],
    FlysystemBundle::class => ['all' => true],
    TienvxMbtBundle::class => ['all' => true],
];
