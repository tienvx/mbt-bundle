<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class Product extends AbstractSubject
{
    public static function support(): string
    {
        return 'product';
    }

    /**
     * @throws Exception
     */
    public function selectFile()
    {
        if (!$this->testing) {
            throw new Exception('Can not upload file!');
        }
    }
}
