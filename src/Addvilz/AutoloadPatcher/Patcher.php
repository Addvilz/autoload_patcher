<?php

namespace Addvilz\AutoloadPatcher;

use Composer\Autoload\ClassLoader;

/**
 * Class Patcher.
 */
class Patcher
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var callable[]
     */
    private $patchers = [];

    /**
     * @param $classLoader
     */
    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * @param string   $className Fully qualified class name you want to patch, including namespace.
     *                            You should not include leading backslash.
     * @param callable $patcher   Callable to invoke to patch the code. Must accept one argument - source code.
     *
     * @return $this
     */
    public function addPatcher($className, callable $patcher)
    {
        if (isset($this->patchers[$className])) {
            throw new \RuntimeException(sprintf(
                'Class %s already has a patcher registered',
                $className
            ));
        }
        $this->patchers[$className] = $patcher;

        return $this;
    }

    /**
     * @param string $className Fully qualified class name you want to patch, including namespace.
     *                          You should not include leading backslash.
     *
     * @return $this
     */
    public function removePatcher($className)
    {
        if (isset($this->patchers[$className])) {
            unset($this->patchers[$className]);
        }

        return $this;
    }

    /**
     * @param string $className Fully qualified class name you want to patch, including namespace.
     *                          You should not include leading backslash.
     *
     * @return bool
     */
    public function hasPatcher($className)
    {
        return isset($this->patchers[$className]);
    }

    /**
     * Register patcher autoloader.
     *
     * @return $this
     */
    public function register()
    {
        spl_autoload_register([$this, 'autoload'], true, true);

        return $this;
    }

    /**
     * Unregister patcher autoloader.
     *
     * @return $this
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'autoload']);

        return $this;
    }

    /**
     * @param string $class Load and patch class
     *
     * @return bool|void
     */
    public function autoload($class)
    {
        if (isset($this->patchers[$class])) {
            $file = $this->classLoader->findFile($class);

            if (!$file) {
                return;
            }

            $contents = preg_replace(
                '/^(<\?php)|(<\?)$/',
                '',
                file_get_contents($file)
            ); // Remove opening PHP tags

            eval($this->patchers[$class]($contents)); // Eval, yayyy!

            return true;
        }

        return;
    }
}
