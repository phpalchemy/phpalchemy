<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\Component\Http;

/**
 * Class Request
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
class JsonResponse extends Response
{
    protected $data;

    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param integer $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct($data = array(), $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->setData($data);
    }

    /**
     * Sets the data to be sent as json.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $this->data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    public function send()
    {
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent($this->data);

        parent::send();
    }
}