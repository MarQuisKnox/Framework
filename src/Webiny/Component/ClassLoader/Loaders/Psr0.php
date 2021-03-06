<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader\Loaders;

/**
 * PSR-0 autoloader.
 *
 * @package         Webiny\Component\ClassLoader\Loaders
 */
class Psr0 extends LoaderAbstract
{
    /**
     * @var LoaderAbstract Holds the loader instance.
     */
    protected static $_instance = null;

    /**
     * Register a map.
     *
     * @param string       $prefix  Map prefix or namespace.
     * @param array|string $library Absolute path to the library or an array with path and additional options.
     *
     * @return void
     */
    public function registerMap($prefix, $library)
    {
        if ($prefix[0] == '\\') {
            $prefix = substr($prefix, 1);
        }

        // check the structure of location if it contains metadata
        if (is_array($library)) {
            $path = $library['Path'];
            $this->_rules[$prefix] = $library;
        } else {
            $path = $library;
        }

        $this->_maps[$prefix] = $path;
    }

    /**
     * Parses that class name and returns the absolute path to the class.
     * NOTE: no file existence checks should be performed, nor should the method require or include the class, is
     * should just return the path to the file.
     *
     * @param string $class Class name that should be loaded.
     *
     * @return string|bool Path to the class or false.
     */
    public function findClass($class)
    {
        if (!$this->_maps) {
            return false;
        }

        $pos = strrpos($class, '\\');
        $namespace = substr($class, 0, $pos);
        $className = substr($class, $pos + 1);

        $normalizedClass = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR . str_replace('_',
                                                                                                                  DIRECTORY_SEPARATOR,
                                                                                                                  $className
            ) . '.php';

        foreach ($this->_maps as $ns => $dir) {
            $pos = stripos($class, $ns);
            if ($pos === false || $pos > 1) {
                continue;
            }

            // create the path to the namespace
            $nsPath = str_replace('\\', DIRECTORY_SEPARATOR, $ns);

            $pos = strpos($normalizedClass, $nsPath);
            if ($pos !== false) {
                $normalizedClass = substr_replace($normalizedClass, '', $pos, strlen($nsPath));
            }

            // build the full path
            $file = rtrim($dir, '/') . DIRECTORY_SEPARATOR . ltrim($normalizedClass, '/');

            return $file;
        }

        return false;
    }
}