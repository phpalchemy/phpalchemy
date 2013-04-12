<?php
namespace Alchemy\Component\UI;

use Alchemy\Component\UI\XmlReader;
use Alchemy\Component\UI\YamlReader;

class ReaderFactory
{
    public function load($file)
    {
        return self::loadReader($file);
    }

    public static function loadReader($file)
    {
        if (!file_exists($file)) {
            throw new \Exception("Error: Meta Web UI File '$file' does not exist.");
        }

        if (!is_readable($file)) {
            throw new \Exception("Error: Meta Web File '$file' is not readable.");
        }

        $reader = null;
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($fileExtension) {
            case 'xml':
                $reader = new XmlReader($file);
                break;
            case 'yaml':
            case 'yml':
                $reader = new YamlReader($file);
                break;
        }

        if (!isset($reader)) {
            throw new \Exception("Error: Couldn't resolve a reader for $file file extension.");
        }

        return $reader;
    }
}

