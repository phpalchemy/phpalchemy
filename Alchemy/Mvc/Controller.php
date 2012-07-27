<?php
namespace Alchemy\Mvc;

use Alchemy\Component\Http\Request;
use Alchemy\Component\Http\Response;
use Alchemy\Component\EventDispatcher\EventSubscriberInterface;
use Alchemy\Kernel\KernelEvents;

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

abstract class Controller
{
    /**
     * Meta object to store all data that will use on templates
     * @var [type]
     */
    public $view = null;
}

