<?php
namespace Realejo\Stdlib;

class ArrayObject implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @var array
     */
    protected $mappedKeys = null;

    /**
     * Define se pode usar propriedades/chaves que não estejam previamente definidas
     *
     * @var boolean
     */
    protected $lockedKeys = true;

    public function __construct($data = null)
    {
        if (is_array($data) && ! empty($data)) {
            $this->populate($data);
        }
    }

    /**
     * @param $key
     * @param bool $reverse
     * @return mixed
     */
    protected function getMappedKey($key, $reverse = false)
    {
        $map = $this->getKeyMapping();
        if (empty($map)) {
            return $key;
        }

        // Verifica se é para desfazer o map
        if ($reverse === true) {
            $map = array_flip($map);
        }
        if (isset($map[$key])) {
            return $map[$key];
        }

        return $key;
    }

    public function populate(array $data)
    {
        if (! empty($data)) {
            foreach ($data as $key => $value) {
                $this->storage[$this->getMappedKey($key)] = $value;
            }
        }
    }

    /**
     * @param bool $unMapKeys
     * @return array
     */
    public function toArray($unMapKeys = true)
    {
        $toArray = [];

        if (empty($this->storage)) {
            return $toArray;
        }

        foreach ($this->storage as $key => $value) {
            if ($value instanceof ArrayObject) {
                $value = $value->toArray($unMapKeys);
            }

            if ($unMapKeys === true) {
                $key = $this->getMappedKey($key, true);
            }
            $toArray[$key] = $value;
        }

        return $toArray;
    }

    public function entityToArray()
    {
        return $this->toArray();
    }

    public function getArrayCopy()
    {
        return $this->toArray();
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $offset = $this->getMappedKey($offset);
        return (array_key_exists($offset, $this->storage));
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $offset = $this->getMappedKey($offset);
        if (! array_key_exists($offset, $this->storage)) {
            //throw new \Exception("Undefined index: $offset in ". var_export($this->storage, true));
            trigger_error("Undefined index: $offset");
        }

        return $this->storage[$offset];
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->getMappedKey($offset);
        if (! $this->getLockedKeys() || array_key_exists($offset, $this->storage)) {
            $this->storage[$offset] = $value;
        } else {
            trigger_error("Undefined index: $offset");
        }
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if ($this->getLockedKeys()) {
            throw new \Exception("You cannot remove a property");
        }

        $offset = $this->getMappedKey($offset);
        unset($this->storage[$offset]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @return array
     */
    public function getKeyMapping()
    {
        return $this->mappedKeys;
    }

    /**
     * @param array $deprecatedMapping
     * @return \Realejo\Stdlib\ArrayObject
     */
    public function setMapping(array $deprecatedMapping = null)
    {
        $this->mappedKeys = $deprecatedMapping;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLockedKeys()
    {
        return $this->lockedKeys;
    }

    /**
     * @param boolean $lockedKeys
     * @return \Realejo\Stdlib\ArrayObject
     */
    public function setLockedKeys($lockedKeys)
    {
        $this->lockedKeys = $lockedKeys;
        return $this;
    }
}
