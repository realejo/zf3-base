<?php

namespace Realejo\Stdlib;

use DateTime;
use Realejo\Enum\Enum;

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

    /**
     * @deprecated use $jsonArrayKeys OR $jsonObjectKeys
     * @var array
     */
    protected $jsonKeys = [];
    protected $jsonArrayKeys = [];
    protected $jsonObjectKeys = [];
    protected $jsonEncodeOptions = 0;

    /**
     * @var Enum[]
     */
    protected $enumKeys = [];

    public function __construct($data = null)
    {
        if (is_array($data) && !empty($data)) {
            $this->populate($data);
        }
    }

    /**
     * @param string $key
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
        $useJsonArrayKeys = (is_array($this->jsonArrayKeys) && !empty($this->jsonArrayKeys));
        $useJsonObjectKeys = (is_array($this->jsonObjectKeys) && !empty($this->jsonObjectKeys));
        $useIntKeys = (is_array($this->intKeys) && !empty($this->intKeys));
        $useBooleanKeys = (is_array($this->booleanKeys) && !empty($this->booleanKeys));
        $useEnumKeys = (is_array($this->enumKeys) && !empty($this->enumKeys));

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($useDateKeys && in_array($key, $this->dateKeys) && !empty($value)) {
                    $value = new DateTime($value);

                } elseif ($useJsonArrayKeys && in_array($key, $this->jsonArrayKeys) && !empty($value)) {
                    $value = json_decode($value, JSON_OBJECT_AS_ARRAY);

                } elseif ($useJsonObjectKeys && in_array($key, $this->jsonObjectKeys) && !empty($value)) {
                    $value = json_decode($value);

                } elseif ($useJsonObjectKeys && in_array($key, $this->jsonKeys) && !empty($value)) {
                    $value = json_decode($value);

                } elseif ($useIntKeys && in_array($key, $this->intKeys) && !empty($value)) {
                    $value = (int)$value;

                } elseif ($useBooleanKeys && in_array($key, $this->booleanKeys) && !empty($value)) {
                    $value = (boolean)$value;

                } elseif ($useEnumKeys && array_key_exists($key, $this->enumKeys)) {
                    $value = new $this->enumKeys[$key]($value);
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
        $toArray = $this->toArray(true);

        if (empty($toArray)) {
            return $toArray;
        }

        foreach ($toArray as $key => $value) {
            // desfaz datetime
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            // desfaz o json
            if (in_array($key, $this->jsonArrayKeys) && is_array($value)) {
                $value = json_encode($value, $this->jsonEncodeOptions);
            }
            if (in_array($key, $this->jsonObjectKeys) && $value instanceof \stdClass) {
                $value = json_encode($value, $this->jsonEncodeOptions);
            }
            if (in_array($key, $this->jsonKeys) && ($value instanceof \stdClass || is_array($value))) {
                $value = json_encode($value, $this->jsonEncodeOptions);
            }

            // desfaz o enum
            if ($value instanceof Enum) {
                $value = $value->getValue();
            }

            // desfaz boolean e int
            if (is_bool($value) || is_int($value)) {
                $value = (int)$value;
            }

            $toArray[$key] = $value;
        }

        return $toArray;
    }

    public function offsetExists($offset)
    {
        $offset = $this->getMappedKey($offset);
        return (array_key_exists($offset, $this->storage));
    }

    public function offsetGet($offset)
    {
        $offset = $this->getMappedKey($offset);
        if (!array_key_exists($offset, $this->storage)) {
            trigger_error("Undefined index: $offset");
        }

        return $this->storage[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $offset = $this->getMappedKey($offset);
        if (!$this->getLockedKeys() || array_key_exists($offset, $this->storage)) {
            $this->storage[$offset] = $value;
        } else {
            trigger_error("Undefined index: $offset");
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->getLockedKeys()) {
            throw new \RuntimeException("You cannot remove a property");
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
     * @param array $mappedKeys
     * @return ArrayObject
     */
    public function setMapping(array $mappedKeys = null)
    {
        $this->mappedKeys = $mappedKeys;
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

    /**
     * @return int
     */
    public function getJsonEncodeOptions(): int
    {
        return $this->jsonEncodeOptions;
    }

    /**
     * @param int $jsonEncodeOptions
     * @return ArrayObject
     */
    public function setJsonEncodeOptions(int $jsonEncodeOptions): ArrayObject
    {
        $this->jsonEncodeOptions = $jsonEncodeOptions;
        return $this;
    }


}
