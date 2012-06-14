<?php
namespace Alchemy\Kernel;

/**
 * This Final Class define all events thrown by Kernel
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 */
final class KernelEvents
{
    /**
     * The REQUEST event
     *
     * This event occurs at the very beginning of request dispatching, it allows
     * you to create a response for a request before any other code in the
     * framework is executed. The event listener method receives a instance of:
     * - Alchemy\Kernel\Event\GetResponseEvent instance.
     *
     * @var string
     */
    const REQUEST = 'kernel.request';

    /**
     * The EXCEPTION event
     *
     * This event occurs when an uncaught exception appears, it allows you to create
     * a response for a thrown exception or to modify the thrown exception.
     * The event listener method receives a instance of:
     * - Alchemy\Kernel\Event\GetResponseForExceptionEvent.
     *
     * @var string
     *
     * @api
     */
    const EXCEPTION = 'kernel.exception';

    /**
     * The VIEW event
     *
     * This event occurs when the return value of a controller is not a Response
     * instance, it allows you to create a response for the return value of the
     * controller. The event listener method receives a instance of:
     * - Alchemy\Kernel\GetResponseForControllerResultEvent instance.
     *
     * @var string
     */
    const VIEW = 'kernel.view';

    /**
     * The CONTROLLER event
     *
     * This event occurs once a controller was found for handling a request, it
     * allows you to change the controller that will handle the request.
     * The event listener method receives a instance of:
     * - Alchemy\Kernel\Event\FilterControllerEvent instance.
     *
     * @var string
     */
    const CONTROLLER = 'kernel.controller';

    /**
     * The RESPONSE event
     *
     * This event occurs once a response was created for replying to a request,
     * it allows you to modify or replace the response that will be
     * replied. The event listener method receives a instance of:
     * - Alchemy\Kernel\Event\FilterResponseEvent instance.
     *
     * @var string
     */
    const RESPONSE = 'kernel.response';

    /**
     * The TERMINATE event
     *
     * This event occurs once a reponse was sent, it allows you to run expensive
     * post-response jobs. The event listener method receives a instance of:
     * - Alchemy\Kernel\Event\PostResponseEvent instance.
     *
     * @var string
     */
    const TERMINATE = 'kernel.terminate';
}
