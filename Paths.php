<?php
/**
 * @package axy\syspaths
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\syspaths;

use axy\errors\ContainerReadOnly;
use axy\syspaths\errors\InvalidPattern;
use axy\syspaths\errors\PatternNotFound;
use axy\syspaths\errors\RequirePatterns;

/**
 * Specifying paths within the system
 *
 * @property-read string $root
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Paths implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * The constructor
     *
     * @param string $root
     *        the root directory
     * @param array $patterns [optionals]
     *        the list of patterns (merges with $this->patterns)
     * @param string $key [optional]
     *        the object name
     * @throws \axy\syspaths\errors\RequirePatterns
     */
    public function __construct($root, array $patterns = null, $key = null)
    {
        $this->root = $root;
        $this->key = $key;
        $this->loadPatterns($patterns);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return (($key === 'root') || isset($this->patterns[$key]));
    }

    /**
     * @param string $key
     * @return bool
     * @throws \axy\syspaths\errors\InvalidPattern
     * @throws \axy\syspaths\errors\PatternNotFound
     */
    public function __get($key)
    {
        if ($key === 'root') {
            return $this->root;
        }
        if (!isset($this->paths[$key])) {
            if (!isset($this->patterns[$key])) {
                throw new PatternNotFound($this->key ? $this->key.'.'.$key : $key);
            }
            $pattern = $this->patterns[$key];
            if (is_array($pattern)) {
                $this->paths[$key] = $this->createSub($pattern, $key);
            } else {
                $this->paths[$key] = $this->create($this->patterns[$key], false);
            }
        }
        return $this->paths[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws \axy\errors\ContainerReadOnly
     */
    public function __set($key, $value)
    {
        throw new ContainerReadOnly('SysPaths', null, $this);
    }

    /**
     * @param string $key
     * @throws \axy\errors\ContainerReadOnly
     */
    public function __unset($key)
    {
        throw new ContainerReadOnly('SysPaths', null, $this);
    }

    /**
     * Creates a path
     *
     * @param string $pattern
     *        the path pattern
     * @param bool $real [optional]
     *        use the realpath()
     * @return string
     * @throws \axy\syspaths\errors\InvalidPattern
     * @throws \axy\syspaths\errors\PatternNotFound
     */
    public function create($pattern, $real = false)
    {
        switch (substr($pattern, 0, 1)) {
            case '/':
                $path = $pattern;
                break;
            case ':':
                $pattern = explode(':', $pattern, 3);
                if (count($pattern) !== 3) {
                    throw new InvalidPattern(':'.$pattern[1], 'require second ":"');
                }
                $path = $this->getKeyPath($pattern[1]).$pattern[2];
                break;
            default:
                $path = $this->root.'/'.$pattern;
        }
        if ($real) {
            $path = realpath($path);
            if ($path === false) {
                $path = null;
            }
        }
        return $path;
    }

    /**
     * Creates a path
     *
     * @param string $pattern
     *        the path pattern
     * @param bool $real [optional]
     *        use the realpath()
     * @return string
     */
    public function __invoke($pattern, $real = false)
    {
        return $this->create($pattern, $real);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach (array_keys($this->patterns) as $k) {
            $this->__get($k);
        }
        return new \ArrayIterator($this->paths);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * {@inheritdoc}
     * @throws \axy\syspaths\errors\InvalidPattern
     * @throws \axy\syspaths\errors\PatternNotFound
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * {@inheritdoc}
     * @throws \axy\errors\ContainerReadOnly
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * {@inheritdoc}
     * @throws \axy\errors\ContainerReadOnly
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->patterns);
    }

    /**
     * @param array $patterns
     * @throws RequirePatterns
     */
    private function loadPatterns($patterns)
    {
        if ($patterns) {
            if ($this->patterns) {
                $this->patterns = array_replace_recursive($this->patterns, $patterns);
            } else {
                $this->patterns = $patterns;
            }
        } elseif (!$this->patterns) {
            throw new RequirePatterns();
        }
    }

    /**
     * @param string $pattern
     * @param string $key
     * @return \axy\syspaths\Paths
     * @throws \axy\syspaths\errors\InvalidPattern
     */
    private function createSub($pattern, $key)
    {
        if ($this->key) {
            $key = $this->key.'.'.$key;
        }
        if (!isset($pattern['root'])) {
            throw new InvalidPattern($key, '"root" not found');
        }
        $root = $this->create($pattern['root'], false);
        unset($pattern['root']);
        if (isset($pattern['__classname'])) {
            $className = $pattern['__classname'];
            unset($pattern['__classname']);
            if (!class_exists($className)) {
                throw new InvalidPattern($key, '__classname "'.$className.'" not found');
            }
        } else {
            $className = __CLASS__;
        }
        return new $className($root, $pattern, $key);
    }

    /**
     * @param string $key
     * @return string|\axy\syspaths\Paths
     * @throws \axy\syspaths\errors\PatternNotFound
     */
    private function getKeyPath($key)
    {
        $current = $this;
        foreach (explode('.', $key) as $k) {
            if (!is_object($current)) {
                throw new PatternNotFound($this->key ? $this->key.'.'.$key : $key);
            }
            $current = $current->__get($k);
        }
        return $current;
    }

    /**
     * @var array
     */
    protected $patterns;

    /**
     * @var array
     */
    private $paths = [];

    /**
     * @var string
     */
    private $root;

    /**
     * @var string
     */
    private $key;
}
