<?php
namespace Alchemy\Service;

use Alchemy\Application;

class ActiveRecordServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Sample class on the Application ServiceProvider
     *
     * @param Application $app phpalchemy Application
     */
    public function register(Application $app)
    {
        $app['sp_ActiveRecords'] = $app->share(function () use ($app) {
            return new ActiveRecordService($app);
        });
    }

    public function init(Application $app)
    {
        //$app['sp_ActiveRecords']->init();
    }
}
