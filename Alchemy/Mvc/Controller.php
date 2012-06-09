<?php
namespace Alchemy\Mvc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function __construct()
    {
    }

    public function __get($name)
    {
        if ($name == 'request') {
            $this->request = new Request();

            return $this->request;
        }

        if ($name == 'response') {
            $this->response = new Response();

            return $this->response;
        }
    }

}