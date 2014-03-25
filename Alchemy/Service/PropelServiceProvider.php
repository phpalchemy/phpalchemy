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

use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;

/**
 * Propel service provider.
 *
 * @author Erik Amaru Ortiz <aortiz.erik@gmail.com>
 */
class PropelServiceProvider implements ServiceProviderInterface
{
    protected $alreadyInit = false;
    protected $classDir = "";
    protected $dataSourceName = "";

    protected $config = array();

    public function register(Application $app)
    {
        $app["propel"] = $app->share(function() use ($app) {
            if (! class_exists('\Propel\Runtime\Propel')) {
                throw new \Exception("Can't register Propel, it is not installed or not loaded!");
            }

            $this->dataSourceName = $app["config"]->get("app.namespace");
            //var_dump("->", $this->dataSourceName); die;

            $this->configure($app);
            $this->initPropel();
        });
    }

    public function init(Application $app)
    {
        $app["propel"]();
    }

    protected function configure(Application $app)
    {
        /** @var \Alchemy\Config $config */
        $config = $app["config"];

        $this->classDir = $config->get("propel.class_dir", $config->get("app.model_dir"));
        $this->config["engine"] = $config->get("database.engine", "");
        $this->config["host"] = $config->get("database.host", "");
        $this->config["port"] = $config->get("database.port", "");
        $this->config["user"] = $config->get("database.user", "");
        $this->config["password"] = $config->get("database.password", "");
        $this->config["dbname"] = $config->get("database.dbname", "");

        $requiredConfig = array("engine", "host", "user", "dbname");

        foreach ($requiredConfig as $keyConf => $valConf) {
            $valConf = trim($valConf);

            if (empty($valConf)) {
                throw new \RuntimeException(sprintf(
                    "Propel Service Provider Error: Configuration missing." . PHP_EOL .
                    "Configuration: \"%s.%s\" is missing or empty.",
                    "database", $keyConf
                ));
            }
        }
    }

    protected function initPropel()
    {
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->setAdapterClass($this->dataSourceName, $this->config["engine"]);
        $manager = new ConnectionManagerSingle();
        $manager->setConfiguration(array (
            "dsn" => $this->config["engine"].":host=".$this->config["host"].";dbname=".$this->config["dbname"],
            "user"     => $this->config["user"],
            "password" => $this->config["password"],
        ));
        $serviceContainer->setConnectionManager($this->dataSourceName, $manager);
        $this->alreadyInit = true;
    }
}
