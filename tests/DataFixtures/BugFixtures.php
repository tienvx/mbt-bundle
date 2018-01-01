<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DataFixtures;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class BugFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $bug1 = new Bug();
        $bug1->setTitle('Bug 1');
        $bug1->setMessage('Something happen on shopping_cart model');
        $bug1->setPaths(['step 1', 'step 2', 'step 3']);
        $bug1->setStatus('unverified');
        $bug1->setTask($this->getReference('task1'));
        $manager->persist($bug1);
        $manager->flush();

        $bug2 = new Bug();
        $bug2->setTitle('Bug 2');
        $bug2->setMessage('Something happen on shopping_cart model');
        $bug2->setPaths(['step 1', 'step 2', 'step 3', 'step 4', 'step 5']);
        $bug2->setStatus('valid');
        $bug2->setTask($this->getReference('task1'));
        $manager->persist($bug2);
        $manager->flush();

        $bug3 = new Bug();
        $bug3->setTitle('Bug 3');
        $bug3->setMessage('Something happen on shopping_cart model');
        $bug3->setPaths(['step 1', 'step 2']);
        $bug3->setStatus('invalid');
        $bug3->setTask($this->getReference('task3'));
        $manager->persist($bug3);
        $manager->flush();
    }
}
