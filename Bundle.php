<?php
namespace Alchemy\Component\WebAssets;

use Alchemy\Component\WebAssets\Filter\FilterInterface;
use Alchemy\Component\WebAssets\Filter\CssMinFilter;
use Alchemy\Component\WebAssets\Filter\JsMinFilter;
use Alchemy\Component\WebAssets\Asset;

class Bundle
{
    protected $outputFilename = '';
    protected $filter = null;
    protected $assets = array();
    protected $meta = array();
    protected $checksum = '';
    protected $cacheDir = '';
    protected $genFilename = '';
    protected $cacheInfo = array();

    public function __construct1()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if ($arg instanceof Asset) {
                $this->assets[] = $arg;
            } elseif ($arg instanceof FilterInterface) {
                if ($this->filter === null) {
                    $this->setFilter($arg);
                } else {
                    throw new \RuntimeException("Runtime Error: A filter is already registered.");
                }
            }
        }
    }

    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_string($arg)) {
                $this->meta[] = array($arg, null);
            } elseif (is_array($arg)) {
                $this->meta[] = $arg;
            }
        }
    }

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function build()
    {
        $checksum = array();
        $id = array();
        $modified = true;
        $this->cacheInfoFile = $this->cacheDir.'/.webassets.cacheinf';

        foreach ($this->meta as $i => $assetInfo) {
            $filename = $assetInfo[0];

            if (! is_string($filename)) {
                throw new \RuntimeException(sprintf(
                    "Runtime Exception: Invalid param. " .
                    "The first param should be a string containing asset file name, '%s given'",
                    $assetInfo[0]
                ));
            }
            if (! file_exists($filename)) {
                throw new \RuntimeException(sprintf("Runtime Exception: File '%s' does not exists", $assetInfo[0]));
            }

            $checksum[] = md5_file($filename);
            $id[] = $filename;
        }


        $this->id = md5(count($id) == 1 ? $id[0] : implode(' ', $id));
        $this->checksum = count($checksum) == 1 ? $checksum[0] : md5(implode('-', $checksum));
        $this->genFilename = count($checksum) == 1 ? basename($filename)
                           : 'x-gen-' . md5($this->checksum) . '.' . pathinfo($filename, PATHINFO_EXTENSION);

        if ($this->revalidate()) {
            $this->saveCacheInf();
        }

        return $this->genFilename;
    }

    public function getUrl()
    {
        return $this->genFilename . '?' . $this->checksum
    }

    public function setOutputFilename($filename)
    {
        $this->outputFilename = $filename;
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function getOutput()
    {
        $this->process();
        //var_dump($this->checksum);
        die;
        $contents = '';

        foreach ($this->assets as $asset) {
            $contents .= "/*! -- File: '".$asset->getFilename()."' -- */\n";
            $contents .= $asset->getOutput();
        }

        return $contents;
    }

    public function revalidate()
    {
        $this->cacheInfoFile = $this->cacheDir . '.webassets.cacheinf';
        $modified = true;

        if (file_exists($this->cacheInfoFile)) {
            $this->cacheInfo = include $this->cacheInfoFile;
        }

        if (array_key_exists($this->id, $this->cacheInfo)) {
            $this->cacheInfo = $this->cacheInfo[$this->id];

            if ($this->checksum === $this->cacheInfo['checksum']) {
                $modified = false;
            }
        }

        return $modified;
    }

    protected function saveCacheInf()
    {
        // save cacheInfo

        if (array_key_exists($this->id, $this->cacheInfo)) {
            if (file_exists($this->cacheInfo[$this->id]['filename'])) {
                // if (@unlink($this->cacheInfo[$this->id]['filename'])) {
                //     echo 'error al borrar old cache file';
                // }
            }
        }

        $this->cacheInfo[$this->id] = array(
            'checksum' => $this->checksum,
            'filename' => $this->genFilename
        );

        $content = '<?php return ' . var_export($this->cacheInfo, true) . ';';
        file_put_contents($this->cacheInfoFile, $content);

        echo "* cache inf. regenerated\n";
    }
}

include 'Asset.php';
include 'File.php';
include 'Filter/FilterInterface.php';
include 'Filter/CssMinFilter.php';
include 'Filter/JsMinPlusFilter.php';
include 'Filter/JsMinFilter.php';

$bundle = new Bundle(
    array('Tests/fixtures/js/before.js', New JsMinFilter),
    'Tests/fixtures/js/issue74.js'
);

$bundle->setCacheDir('Tests/cache');
$genFile = $bundle->build();
var_dump($genFile);

//-------------------

$bundle = new Bundle('Tests/fixtures/js/issue74.js');

$bundle->setCacheDir('Tests/cache');
$genFile = $bundle->build();
var_dump($genFile);
