<?php
namespace Realejo\Stdlib;

class ArrayObject implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $storage = array();

    /**
     * @var array
     */
    protected $deprecatedKeys = null;

    /**
     * Define se pode usar propriedades/chaves que não estejam previamente definidas
     *
     * @var boolean
     */
    protected $lockedKeys = true;

    public function __construct($data = null)
    {
        if (is_array($data) && !empty($data)) {
            $this->populate($data);
        }
    }

    protected function getDeprecatedKey($key)
    {
        $map = $this->getDeprecatedMapping();
        if (empty($map)) {
            return $key;
        }

        if (isset($map[$key])) {
            return $map[$key];
        }

        return $key;
    }

    public function populate(array $data)
    {
        if (!empty($data)) {
            foreach($data as $key=>$value) {
                $this->storage[$this->getDeprecatedKey($key)] = $value;
            }
        }
    }

    public function toArray()
    {
        $toArray = array();

        if (empty($this->storage)) {
            return $toArray;
        }

        foreach($this->storage as $key=>$value) {
            if ($value instanceof \Realejo\Stdlib\ArrayObject) {
                $value = $value->toArray();
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
        $offset = $this->getDeprecatedKey($offset);
        return (array_key_exists($offset, $this->storage));
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $offset = $this->getDeprecatedKey($offset);
        if (!array_key_exists($offset, $this->storage)) {
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
        $offset = $this->getDeprecatedKey($offset);
        if (!$this->getLockedKeys() || array_key_exists($offset, $this->storage)) {
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

        $offset = $this->getDeprecatedKey($offset);
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
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * @return array
     */
    public function getDeprecatedMapping()
    {
        return $this->deprecatedKeys;
    }

    /**
     * @param array $deprecatedMapping
     * @return \Realejo\Stdlib\ArrayObject
     */
    public function setDeprecatedMapping(array $deprecatedMapping = null)
    {
        $this->deprecatedKeys = $deprecatedMapping;

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
