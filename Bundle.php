<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Component\WebAssets;

use Alchemy\Component\WebAssets\Filter\FilterInterface;
use Alchemy\Component\WebAssets\Filter\CssMinFilter;
use Alchemy\Component\WebAssets\Filter\JsMinFilter;
use Alchemy\Component\WebAssets\Asset;

/**
 * Class Bundle
 *
 * This class handle assets
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/WebAssets
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/WebAssets
 */
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
    protected $locateDirs = array();

    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            $this->add($arg);
        }

        foreach ($this->meta as $i => $assetInfo) {
            if (! is_string($assetInfo[0])) {
                throw new \RuntimeException(sprintf(
                    "Runtime Exception: Invalid param. " .
                    "The first param should be a string containing asset file name, '%s given'",
                    $assetInfo[0]
                ));
            }
        }
    }

    public function add($filename, $filter = null)
    {
        if (! is_null($filter) && ! ($filter instanceof FilterInterface)) {
            throw new \RuntimeException("Runtime Exception: Invalid Filter, it must implement FilterInterface.");
        }

        if (is_string($filename)) {
            $this->meta[] = array($filename, $filter);
        } elseif (is_array($filename)) {
            $this->meta[] = $filename;
        } else {
            throw new \RuntimeException(sprintf(
                "Runtime Exception: Invalid param. " .
                "The first param should be a string containing asset file name, '%s given'",
                $filename
            ));
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

    public function setLocateDir($dir)
    {
        if (is_array($dir)) {
            $this->locateDirs = array_merge($this->locateDirs, $dir);
        } else {
            $this->locateDirs[] = $dir;
        }
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
            foreach ($this->locateDirs as $dir) {
                if (file_exists($dir . $assetInfo[0])) {
                    $filename = $this->meta[$i][0] = $dir . $assetInfo[0];
                    break;
                }
            }

            if (! file_exists($filename)) {
                throw new \RuntimeException(sprintf("Runtime Exception: File '%s' does not exists", $filename));
            }

            $checksum[] = md5_file($filename);
            $id[] = $assetInfo[0];
        }

        $this->id = md5(count($id) == 1 ? $id[0] : implode(' ', $id));

        if (count($checksum) == 1) {
            $this->checksum = $checksum[0];
            $this->genFilename = basename($filename);
        } else {
            $this->checksum = md5(implode('-', $checksum));
            $this->genFilename = 'x-gen-'.md5($this->checksum).'.'.pathinfo($filename, PATHINFO_EXTENSION);
        }

        $this->loadCache();

        if ($this->isCached() && ! $this->isCacheOutdated()) {
            return file_get_contents($this->cacheInfo[$this->id]['filename']);
        } else {
            $this->saveCacheInf();
        }

        foreach ($this->meta as $item) {
            $asset = new Asset($item[0], $item[1]);

            $this->output .= "/*! -- File: '".$asset->getFilename()."' -- */\n";
            $this->output .= $asset->getOutput() . "\n";
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

