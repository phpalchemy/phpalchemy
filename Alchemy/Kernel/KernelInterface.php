<?php
namespace Alchemy\Kernel;

use Alchemy\Application;
use Alchemy\Component\Http\Request;

/**
 * KernelInterface handles a Request to convert it to a Response.
 */
interface KernelInterface
{
    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param \Alchemy\Component\Http\Request $request A Request instance
     * @param \Alchemy\Application $app
     * @return \Alchemy\Component\Http\Response A Response instance
     */
    public function handle(Request $request, Application $app = null);
}

