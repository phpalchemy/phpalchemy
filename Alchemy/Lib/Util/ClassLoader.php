<?php
namespace Alchemy\lib\Util;

/**
 * Class Route
 *
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
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
     * Hold single object
     *
     * @var ClassLoader
     */
    protected static $_instance = null;


    private $_namespace   = '';
    private $_includePaths = array();
    private $_includeVirtualPaths = array();

    private static $_nsSep  = '\\';
    private static $_dirSep = DIRECTORY_SEPARATOR;

    private static $_fileExtension = '.php';

    /**
     * Creates a new SplClassLoader and installs the class on the SPL autoload stack
     *
     * @param string $ns The namespace to use.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     *
     * @param string $sep The separator to use.
     */
    public function setNamespaceSeparator($sep)
    {
        $this->_namespaceSeparator = $sep;
    }

    /**
     * Gets the namespace seperator used by classes in the namespace of this class loader.
     *
     * @return void
     */
    public function getNamespaceSeparator()
    {
        return $this->_namespaceSeparator;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     */
    public function getIncludePath()
    {
        return $this->_includePath;
    }

    /**
     * Sets the file extension of class files in the namespace of this class loader.
     *
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
    }

    /**
     * Gets the file extension of class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     */
    public function getFileExtension()
    {
        return $this->_fileExtension;
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

        $this->includePaths[$namespace] = rtrim($includePath, self::$_dirSep) . self::$_dirSep;
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
    public function loadClass($className)
    {
        if (class_exists($className)) {
            return true;
        }

        if (false !== strpos($className, '\\')) {
            $className = str_replace(self::$_nsSep, self::$_dirSep, ltrim($className, '\\'));
        }

        $filename = str_replace('_', self::$_dirSep, $className) . self::$_fileExtension;

        foreach ($this->includePaths as $namespace => $includePath) {
            if (strpos($namespace, ',') !== false) {
                list($namespace, $excludeNsPart) = explode(',', $namespace);

                $nsDirMapped = str_replace(self::$_nsSep, self::$_dirSep, $excludeNsPart);
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