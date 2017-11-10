<?php
namespace Realejo\Stdlib;

use Zend\Json\Json;

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

    protected $intKeys = [];

    protected $booleanKeys = [];

    protected $dateKeys = [];

    protected $jsonKeys = [];

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
        $useDateKeys = (is_array($this->dateKeys) && !empty($this->dateKeys));
        $useJsonKeys = (is_array($this->jsonKeys) && !empty($this->jsonKeys));
        $useIntKeys = (is_array($this->intKeys) && !empty($this->intKeys));
        $useBooleanKeys = (is_array($this->booleanKeys) && !empty($this->booleanKeys));

        if (! empty($data)) {
            foreach ($data as $key => $value) {
                if ($useDateKeys && in_array($key, $this->dateKeys) && !empty($value)) {
                    $value = new \DateTime($value);
                } elseif ($useJsonKeys && in_array($key, $this->jsonKeys) && !empty($value)) {
                    $value = Json::decode($value);
                } elseif ($useIntKeys && in_array($key, $this->intKeys) && !empty($value)) {
                    $value = (int) $value;
                } elseif ($useBooleanKeys && in_array($key, $this->booleanKeys) && !empty($value)) {
                    $value = (boolean) $value;
                }
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

            // desfaz datetime
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            // desfaz o json
            if (in_array($key, $this->jsonKeys) && $value instanceof \stdClass) {
                $value = json_encode($value, JSON_OBJECT_AS_ARRAY);
            }

            // desfaz boolean e int
            if (is_bool($value) || is_numeric($value)) {
                $value = (int) $value;
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
     * @return ArrayObject
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
     * @return ArrayObject
     */
    public function setLockedKeys($lockedKeys)
    {
        $this->lockedKeys = $lockedKeys;
        return $this;
    }
}
