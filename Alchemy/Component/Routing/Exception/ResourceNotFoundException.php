<?php
namespace Alchemy\Component\Routing\Exception;

class ResourceNotFoundException extends \Exception
{
    public $url;

    /**
     * Constructor.
     * @param string $url url of request
     */
    public function __construct($url)
    {
        $this->url = $url;
        parent::__construct(sprintf('Resource "%s" Not Found!', $url));
    }
}