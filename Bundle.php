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
    protected $fromCache = false;
    protected $path = '';

    public function __construct()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            $this->add($arg);
        }
    }

    public function getPath()
    {
        return $this->path;
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

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = rtrim(realpath($cacheDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function setOutputDir($outputDir)
    {
        $this->outputDir = rtrim(realpath($outputDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function isFromCache()
    {
        return $this->fromCache;
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
        return $this->output;
    }

    public function add($filename, $filter = null)
    {
        if (! empty($filter) && ! ($filter instanceof FilterInterface)) {
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

    public function handle()
    {
        // first verify if a single resource file without filter was requested
        if (count($this->meta) == 1 && empty($this->meta[0][1])) {
            $this->path = $this->locateFile($this->meta[0][0]);

            return true;
        }

        // continue processing multiples resources or a single filtered res.
        $checksum = array();
        $id = array();
        $this->cacheInfoFile = $this->cacheDir . '.webassets.cacheinf';

        foreach ($this->meta as $i => $assetInfo) {
            $filename = $this->meta[$i][0] = $this->locateFile($assetInfo[0]);

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
            $this->fromCache = true;
            $this->path = $this->cacheInfo[$this->id]['filename'];

            return true;
        }

        $this->saveCacheInf();

        // build asset compiled file.

        foreach ($this->meta as $item) {
            $asset = new Asset($item[0], $item[1]);

            $this->output .= "/*! -- File: '".$asset->getFilename()."' -- */\n";
            $this->output .= $asset->getOutput() . "\n";
        }

        // save generated file
        $this->path = $this->outputDir . $this->genFilename;

        if (@file_put_contents($this->path, $this->output) === false) {
            throw new \RuntimeException(sprintf(
                "Runtime Error: WebAssets couldn't save generated file in '%s' directory.", $this->outputDir
            ));
        }
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
    }

    protected function locateFile($src)
    {
        foreach ($this->locateDirs as $dir) {
            if (file_exists($dir . $src)) {
                return $dir . $src;
            }
        }

        throw new \RuntimeException(sprintf("Runtime Exception: File '%s' does not exist!", $src));
    }
}

