<?php
namespace Alchemy\Component\ClassLoader;

/**
 * Class Route
 *
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * Singleton Class
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   Alchemy/Component/ClassLoader
 */
class ClassLoader
{
    /**
     * Holds singleton object
     *
     * @var ClassLoader
     */
    protected static $instance = null;
    protected $includePaths = array();

    /**
     * Creates a new SplClassLoader and installs the class on the SPL autoload stack
     *
     * @param string $ns The namespace to use.
     */
    public function __construct()
    {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('NS') || define('NS', '\\');

        spl_autoload_register(array($this, 'loadClass'));
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * register a determinated namespace and its include path to find it.
     *
     * @param string $namespace namespace of a class given
     * @param string $includePath path where the class exists
     * @param string $excludeNsPart contais a part of class to exclude from classname passed by SPL hanlder
     */
    public function register($namespace, $includePath, $excludeNsPart = '')
    {
        if (!empty($excludeNsPart)) {
            $namespace .= ',' . $excludeNsPart;
        }

        $this->includePaths[$namespace] = rtrim($includePath, DS) . DS;
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     * @return void
     */
    protected function loadClass($className)
    {
        if (strpos($className, NS) !== false) {
            $className = str_replace(NS, DS, ltrim($className, NS));
        }

        $filename = str_replace('_', DS, $className) . '.php';

        foreach ($this->includePaths as $namespace => $includePath) {
            if (strpos($namespace, ',') !== false) {
                list($namespace, $excludeNsPart) = explode(',', $namespace);
                $nsDirMapped = str_replace(NS, DS, $excludeNsPart);
                $filename    = str_replace($nsDirMapped, '', $filename);
            }

            if (file_exists($includePath . $filename)) {
                require_once $includePath . $filename;

                return true;
            }
        }

        return false;
    }
}

