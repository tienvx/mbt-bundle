<?php

namespace App\Subject;

use Exception;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class Product extends Subject
{
    /**
     * @throws Exception
     */
    public function selectFile()
    {
        if (!$this->testing) {
            throw new Exception('Upload required!');
        }
    }
}
