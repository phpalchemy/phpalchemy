<?php
namespace Alchemy\Mvc;

use Alchemy\Net\Http\Request;
use Alchemy\Net\Http\Response;

/**
 * Controller
 *
 * This is the parent class to support controllers at MVC Pattern
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */

class Controller
{
    /**
     * Meta object to store all data that will use on templates
     * @var [type]
     */
    public $view = null;

    protected $reponse = null;
    protected $request = null;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        if (empty($this->response)) {
            $this->response = new Response();
        }

        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}