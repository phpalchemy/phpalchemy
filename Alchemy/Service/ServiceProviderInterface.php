<?php
namespace Alchemy\Service;

use Alchemy\Application;

/**
 * Interface that must implement all service providers.
 */
interface ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app);

    /**
     * Initialize the service.
     *
     * This method is called after all services are registers and should be used
     * for dynamic configuration and others particular tasks for the service.
     */
    public function init(Application $app);
}