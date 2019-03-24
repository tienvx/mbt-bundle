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

    public function getScreenshotUrl($bugId, $index)
    {
        return sprintf('http://localhost/mbt-api/bug-screenshot/%d/%d', $bugId, $index);
    }
}
