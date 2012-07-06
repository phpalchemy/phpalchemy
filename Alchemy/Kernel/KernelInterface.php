<?php
namespace Alchemy\Kernel;

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
     * @param  Request $request A Request instance
     * @return Response A Response instance
     * @throws \Exception When an Exception occurs during processing
     */
    public function handle(Request $request);
}

