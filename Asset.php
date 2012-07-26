<?php
namespace WebAssets;

use WebAssets\Filter\FilterInterface;
use WebAssets\File;

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

    public function getOutput()
    {
        $contents = $this->file->getContent();

        if (! empty($this->filter)) {
            $contents = $this->filter->apply($contents);
        }

        return $contents;
    }

    public function getFilename()
    {
        return $this->file->getFilename();
    }
}

