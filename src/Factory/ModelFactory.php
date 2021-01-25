<?php

namespace Tienvx\Bundle\MbtBundle\Factory;

use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Factory\Model\RevisionFactory;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

class ModelFactory
{
    public static function createFromArray(array $data): ModelInterface
    {
        $model = new Model();
        $model->setLabel($data['label'] ?? '');
        $model->setTags($data['tags'] ?? null);
        $model->setActiveRevision(RevisionFactory::createFromArray([
            'places' => $data['places'] ?? [],
            'transitions' => $data['transitions'] ?? [],
        ]));

        return $model;
    }
}
