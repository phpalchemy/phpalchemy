<?php

/**
 * This file is part of the PropelServiceProvider package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Alchemy\Service;

use Alchemy\Application;
use Alchemy\ServiceProviderInterface;

/**
 * Propel service provider.
 *
 * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class PropelServiceProvider implements ServiceProviderInterface
{
    protected $alreadyInit = false;

    public function register(Application $app)
    {
        if (isset($app['propel.model_path']) && isset($app['propel.config_file'])) {
            $this->initPropel($app);
        }
    }

    public function init(Application $app)
    {
        if (!$this->alreadyInit) {
            $this->initPropel($app);
        }
    }

    protected function initPropel(Application $app)
    {
        if (!class_exists('Propel')) {
            require_once $this->guessPropel($app);
        }

        $modelPath = $this->guessModelPath($app);
        $config    = $this->guessConfigFile($app);

        \Propel::init($config);
        set_include_path($modelPath . PATH_SEPARATOR . get_include_path());

        $this->alreadyInit = true;
    }
}
