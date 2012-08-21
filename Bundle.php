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
    protected $genFilename = '';
    protected $cacheInfo = array();
    protected $output = '';

    protected $cacheDir  = '';
    protected $outputDir = '';

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
        $this->cacheDir = rtrim(realpath($cacheDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function setOutputDir($outputDir)
    {
        $this->outputDir = rtrim(realpath($outputDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function getOutput()
    {
        if (! empty($this->output)) {
            return $this->output;
        }

        $checksum = array();
        $id = array();
        $this->cacheInfoFile = $this->cacheDir . '.webassets.cacheinf';

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

        $this->loadCache();

        if ($this->isCached() && ! $this->isCacheOutdated()) {
            return file_get_contents($this->cacheInfo[$this->id]['filename']);
        } else {
            $this->saveCacheInf();
        }

        foreach ($this->meta as $item) {
            $asset = new Asset($item[0], $item[1]);

            $this->output .= "\n/*! -- File: '".$asset->getFilename()."' -- */\n";
            $this->output .= $asset->getOutput();
        }

        return $this->output;
    }

    protected function loadCache()
    {
        if (! file_exists($this->cacheInfoFile)) {
            return false;
        }

        $this->cacheInfo = include $this->cacheInfoFile;

        // verify is cacheInfo data is corrupted, it should be an array
        if (! is_array($this->cacheInfo)) {
            @unlink($this->cacheInfoFile);
            $this->cacheInfo = array();
        }
    }

    protected function isCached()
    {
        if (
            array_key_exists($this->id, $this->cacheInfo) &&
            array_key_exists('filename', $this->cacheInfo[$this->id]) &&
            array_key_exists('checksum', $this->cacheInfo[$this->id]) &&
            file_exists($this->cacheInfo[$this->id]['filename'])
        ) {
            return true;
        }

        return false;
    }

    protected function isCacheOutdated()
    {
        return ($this->checksum !== $this->cacheInfo[$this->id]['checksum']);
    }

    protected function isOutdated()
    {
        if (! file_exists($this->cacheInfoFile)) {
            return true;
        }

        $this->cacheInfo = include $this->cacheInfoFile;

        // verify is cacheInfo data is corrupted, it should be an array
        if (! is_array($this->cacheInfo)) {
            return true;
        } elseif (
            ! array_key_exists('checksum', $this->cacheInfo) ||
            ! array_key_exists('filename', $this->cacheInfo)
        ) {
            return true;
        }

        if (array_key_exists($this->id, $this->cacheInfo)) {
            $this->cacheInfo = $this->cacheInfo[$this->id];

            if ($this->checksum === $this->cacheInfo['checksum']) {
                return false;
            }
        }

        return true;
    }

    public function getUrl()
    {
        return $this->genFilename . '?' . $this->checksum;
    }

    public function setOutputFilename($filename)
    {
        $this->outputFilename = $filename;
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function save()
    {
        $output = $this->getOutput();

        if (@file_put_contents($this->outputDir . $this->genFilename, $output) !== false) {
            return $this->outputDir . $this->genFilename;
        }

        return false;
    }

    protected function saveCacheInf()
    {
        if (array_key_exists($this->id, $this->cacheInfo)) {
            if (file_exists($this->cacheInfo[$this->id]['filename'])) {
                @unlink($this->cacheInfo[$this->id]['filename']);
            }
        }

        $this->cacheInfo[$this->id] = array(
            'checksum' => $this->checksum,
            'filename' => $this->outputDir . $this->genFilename
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
include 'Filter/JsMinFilter.php';

$bundle = new Bundle(
    array('Tests/fixtures/js/before.js', New JsMinFilter),
    'Tests/fixtures/js/issue74.js'
);

$bundle->setCacheDir('Tests/cache');
$bundle->setOutputDir('Tests/cache');

if ($genFile = $bundle->save())
    echo 'File writed as ' . $genFile."\n";
else
    echo "Could't save file: $genFile\n";

