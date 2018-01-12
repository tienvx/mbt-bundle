<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Symfony\Component\Finder\Finder;

class FileHelper
{
    private function getFullNamespace($filename)
    {
        $lines = file($filename);
        $namespaces = preg_grep('/^namespace /', $lines);
        $namespaceLine = array_shift($namespaces);
        $match = array();
        preg_match('/^namespace (.*);$/', $namespaceLine, $match);
        $fullNamespace = array_pop($match);

        return $fullNamespace;
    }

    private function getClassname($filename)
    {
        $directoriesAndFilename = explode('/', $filename);
        $filename = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $filename);
        $className = array_shift($nameAndExtension);

        return $className;
    }

    private function getFilenames($dirs)
    {
        $finderFiles = Finder::create()->files()->in($dirs)->name('*.php');
        $filenames = array();
        foreach ($finderFiles as $finderFile) {
            $filenames[] = $finderFile->getRealpath();
        }

        return $filenames;
    }

    public function getAllFcqns(array $dirs)
    {
        $filenames = $this->getFilenames($dirs);
        $fcqns = array();
        foreach ($filenames as $filename) {
            $fcqns[] = $this->getFullNamespace($filename) . '\\' . $this->getClassname($filename);
        }

        return $fcqns;
    }
}
