<?php

namespace Tienvx\Bundle\MbtBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;

class BugFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var ReproducePath $reproducePath1 */
        $reproducePath1 = $this->getReference('reproducePath1');
        /** @var ReproducePath $reproducePath2 */
        $reproducePath2 = $this->getReference('reproducePath2');

        $bug1 = new Bug();
        $bug1->setTitle('Bug 1');
        $bug1->setStatus('unverified');
        $bug1->setReproducePath($reproducePath1);
        $manager->persist($bug1);

        $bug2 = new Bug();
        $bug2->setTitle('Bug 2');
        $bug2->setStatus('valid');
        $bug2->setReproducePath($reproducePath2);
        $manager->persist($bug2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ReproducePathFixtures::class,
        ];
    }
}
