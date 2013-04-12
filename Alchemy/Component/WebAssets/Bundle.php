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
    protected $forceReset = true;

    protected $cacheDir  = '';
    protected $outputDir = '';
    protected $baseDir = '';
    protected $locateDirs = array();
    protected $fromCache = false;
    protected $path = '';
    protected $vendorDir = '';

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
        return str_replace($this->baseDir, '', $this->path) . '?'.$this->checksum;
    }

    public function setOutputFilename($filename)
    {
        $this->outputFilename = $filename;
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function setForceReset($value)
    {
        $this->forceReset = $value;
    }

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = rtrim(realpath($cacheDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! is_dir($this->cacheDir)) {
            self::createDir($this->cacheDir);
        }
    }

    public function setOutputDir($outputDir)
    {
        $this->outputDir = rtrim(realpath($outputDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! is_dir($this->cacheDir)) {
            self::createDir($this->cacheDir);
        }
    }

    public function setBaseDir($baseDir)
    {
        $this->baseDir = rtrim(realpath($baseDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! is_dir($this->baseDir)) {
            self::createDir($this->baseDir);
        }
    }

    public function setLocateDir($dir)
    {
        if (is_array($dir)) {
            $this->locateDirs = array_merge($this->locateDirs, $dir);
        } else {
            $this->locateDirs[] = $dir;
        }
    }

    public function setVendorDir($vendorDir)
    {
        $this->vendorDir = rtrim(realpath($vendorDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;;
    }

    public function isFromCache()
    {
        return $this->fromCache;
    }

    public function getOutput()
    {
        return $this->output;
    }

    /**
     * This method adds a asset and import a asset library is was requested
     * @param mutable some params can be passed with some valid prefixes like file:some_file, filter:some_filter_name
     *                and import:some_import_dir_or_pattern, the order of params doesn't matter
     *
     * example:
     *  $this->register('file:css/bootstrap.css', 'import:twitter/bootstrap/*')
     *  $this->register('import:twitter/jquery/*.js', 'file:css/bootstrap.css')
     */
    public function register()
    {
        $numArgs = func_get_args();

        if ($numArgs === 0) {
            throw new \RuntimeException("Assets Bundle Error: Register error, missing params.");
        }

        $args = func_get_args();
        $params = array();

        if (count($args) === 1) {
            if (strpos($args[0], ':') === false) {
                $args[0] = 'file:' . $args[0];
            }
        }

        foreach ($args as $i => $arg) {
            if (strpos($args[0], ':') === false) {
                throw new \RuntimeException(sprintf(
                    "Assets Bundle Error: Invalid param, multiple params must be " .
                    "prefixed for a valid key (file|filter|import). %s", print_r($args, true)
                ));
            }

            list($key, $value) = explode(':', $arg);
            $params[trim($key)] = trim($value);

            if (! in_array($key, array('file', 'filter', 'import'))) {
                throw new \RuntimeException(sprintf(
                    "Assets Bundle Error: Invalid key '%s', valid keys are: (%s).", $key, 'file|filter|import'
                ));
            }
        }

        if (! array_key_exists('file', $params)) {
            throw new \RuntimeException("Assets Bundle Error: asset file name param is missing.");
        }

        if (array_key_exists('filter', $params) && ! ($params['filter'] instanceof FilterInterface)) {
            throw new \RuntimeException("Assets Bundle Error: Invalid Filter, it must implement FilterInterface.");
        } else {
            $params['filter'] = null;
        }

        if (array_key_exists('import', $params)) {
            // do import
            if (strpos($params['import'], '->') !== false) {
                list($source, $target) = explode('->', $params['import']);
                $source = trim($source);
                $target = trim($target);
            } else {
                $source = $params['import'];
                $target = '';
            }

            $this->import($source, $target);
        }

        // add asset
        $this->add($params['file'], $params['filter']);
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
        if (count($this->meta) == 1 && empty($this->meta[0][1]) && strstr($this->meta[0][0], $this->baseDir) === false) {
            $this->path = $this->locateFile($this->meta[0][0]);
            $this->genFilename = $this->meta[0][0];
            $this->checksum    = filemtime($this->path);

            if ($this->forceReset) {
                $this->meta = array();
            }

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

        $this->id = count($id) == 1 ? $id[0] : md5(implode(' ', $id));

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

        if ($this->forceReset) {
            $this->meta = array();
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
            if (file_exists($dir.'/'.$src)) {
                return $dir.'/'.$src;
            }
        }

        throw new \RuntimeException(sprintf("Runtime Exception: File '%s' does not exist! on %s", $src, print_r($this->locateDirs, true)));
    }

    public function import($vendor, $targetDir = '')
    {
        if (empty($targetDir)) {
            if (strpos($vendor, '*') !== false) {
                $parts =  explode(DIRECTORY_SEPARATOR, $vendor);
                $i = 0;
                while (strpos($parts[$i], '*') === false) {
                    $targetDir = $parts[$i++];
                }
            } else {
                $targetDir = basename($vendor);
            }
        }

        $target = $this->baseDir . 'assets/lib/'.$targetDir.'/';

        if (is_dir($target)) {
            return true;
        }

        self::createDir($target);

        ///
        $vendorExists = false;
        if (strpos($vendor, '*') !== false && ($files = glob($this->vendorDir . $vendor, GLOB_ERR)) !== false) {
            $vendorExists = true;
        } elseif (is_dir($this->vendorDir . $vendor) || is_file($this->vendorDir . $vendor)) {
            $files = $this->vendorDir . $vendor;
            $vendorExists = true;
        }

        if (! $vendorExists) {
            throw new \RuntimeException(sprintf(
                "Assets Bundle Error: Vendor '%' does not exist.",
                $vendor
            ));
        }

        if (is_array($files)) {
            foreach ($files as $f) {
                self::smartCopy($f, $target);
            }
        } else {
            self::smartCopy($files, $target);
        }
    }

    protected static function createDir($strPath, $rights = 0777)
    {
        $folderPath = array($strPath);
        $oldumask   = umask(0);
        $terminalPath = array(DIRECTORY_SEPARATOR, '.', '');

        while (! is_dir(dirname(end($folderPath))) && ! in_array(dirname(end($folderPath)), $terminalPath)) {
            array_push($folderPath, dirname(end($folderPath)));
        }

        while ($parentFolderPath = array_pop($folderPath)) {
            if (! @is_dir($parentFolderPath)) {
                if (! @mkdir($parentFolderPath, $rights)) {
                    throw new \Exception("Runtime Error: Can't create folder '$parentFolderPath'");
                }
            }
        }

        umask($oldumask);
    }

    protected static function rcopy($path, $dest)
    {
        if (is_array($path)) {
            foreach ($path as $file) {
                self::rcopy($path, $dest);
            }
        } elseif (is_dir($path)){
            if (! is_dir($dest)) {
                self::createDir($dest);
            }
            $objects = scandir($path);

            if (count($objects) > 0) {
                foreach ($objects as $file) {
                    if ($file == "." || $file == "..") {
                        continue;
                    }
                    // go on
                    if (is_dir($path.DIRECTORY_SEPARATOR.$file)) {
                        self::rcopy($path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file);
                    } else {
                        copy($path.DIRECTORY_SEPARATOR.$file, $dest.DIRECTORY_SEPARATOR.$file);
                    }
                }
            }

            return true;
        } elseif (is_file($path)) {
            return copy($path, $dest);
        } else {
            return false;
        }
    }

    protected static function smartCopy($source, $dest, $options = array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result = false;

        if (is_file($source)) {
            if ($dest[strlen($dest)-1] == '/') {
                if (! file_exists($dest)) {
                    self::createDir($dest, $options['folderPermission']);
                }

                $newDest = $dest."/".basename($source);
            } else {
                $newDest = $dest;
            }

            $result = copy($source, $newDest);
            chmod($newDest, $options['filePermission']);
        } elseif (is_dir($source)) {
            if ($dest[strlen($dest)-1] == '/') {
                if ($source[strlen($source)-1] == '/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest = $dest.basename($source);
                    @mkdir($dest);
                    chmod($dest, $options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1] == '/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest, $options['folderPermission']);
                    chmod($dest, $options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest, $options['folderPermission']);
                    chmod($dest, $options['filePermission']);
                }
            }

            $dirHandle = opendir($source);
            while ($file = readdir($dirHandle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($source."/".$file)) {
                        $newDest = $dest."/".$file;
                    } else {
                        $newDest = $dest."/".$file;
                    }
                    $result = self::smartCopy($source."/".$file, $newDest, $options);
                }
            }
            closedir($dirHandle);
        } else {
            $result = false;
        }

        return $result;
    }
}

