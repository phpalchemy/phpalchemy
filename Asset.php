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
use Alchemy\Component\WebAssets\File;

/**
 * Class Asset
 *
 * This class handle a single asset file with its filters
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/WebAssets
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/WebAssets
 */
class Asset
{
    /**
     * Holds asset file name
     * @var string
     */
    protected $filename = '';

    /**
     * Handles the asset file
     * @var WebAssets\File
     */
    protected $file = null;

    /**
     * Stores the filter for the current asset
     * @var WebAssets\Filter\FilterInterface
     */
    protected $filter;

    public function __construct($filename, FilterInterface $filter = null)
    {
        if (! file_exists($filename)) {
            throw new \Exception(sprintf("Runtime Error: Asset File '%s' does not exist!", $filename));
        }

        $this->filename = $filename;
        $this->filter = $filter;

        $this->file = new File($this->filename);
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function hasFilter()
    {
        return ! empty($this->filter);
    }

    public function getOutput()
    {
        $contents = $this->file->getContent();

        if ($this->hasFilter()) {
            $contents = $this->filter->apply($contents);
        }

        return $contents;
    }

    public function getFilename()
    {
        return $this->file->getFilename();
    }
}

