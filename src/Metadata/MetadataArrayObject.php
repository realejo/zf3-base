<?php
namespace Realejo\Metadata;

class MetadataArrayObject implements \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @var string
     */
    protected $nullKeys = ':';

    public function __construct(array $data = null)
    {
        if (! empty($data)) {
            $this->populate($data);
        }
    }

    public function count()
    {
        return count($this->storage) + substr_count($this->nullKeys, ':') - 1;
    }

    public function populate(array $data)
    {
        // remove as chaves vazias
        if (! empty($data)) {
            foreach ($data as $key => $value) {
                if (is_null($value)) {
                    $this->nullKeys .= $key . ':';
                    unset($data[$key]);
                }
            }
        }
        $this->storage = $data;
    }

    public function toArray()
    {
        $toArray = $this->storage;
        if (strlen($this->nullKeys) > 1) {
            foreach (explode(':', trim($this->nullKeys, ':')) as $key) {
                $toArray[$key] = null;
            }
        }
        return $toArray;
    }

    /**
     * @param array $metadata
     */
    public function addMetadata($metadata)
    {
        // remove as chaves vazias
        if (! empty($metadata)) {
            foreach ($metadata as $key => $value) {
                if (is_null($value)) {
                    $this->nullKeys .= $key . ':';
                    unset($metadata[$key]);
                } else {
                    $this->storage[$key] = $value;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->storage)) {
            return true;
        }

        if (isset($this->nullKeys) && strpos($this->nullKeys, ":$offset:") !== false) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->storage)) {
            return $this->storage[$offset];
        }

        if (isset($this->nullKeys) && strpos($this->nullKeys, ":$offset:") !== false) {
            return null;
        }

        trigger_error("Undefined index: $offset");
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (array_key_exists($offset, $this->storage)) {
            if (is_null($value)) {
                $this->nullKeys .= "$offset:";
                unset($this->storage[$offset]);
            } else {
                $this->storage[$offset] = $value;
            }
            return;
        }

        if (isset($this->nullKeys) && strpos($this->nullKeys, ":$offset:") !== false) {
            $this->storage[$offset] = $value;
            $this->nullKeys = str_replace(":$offset:", ':', $this->nullKeys);
            return $this;
        }

        trigger_error("Undefined index: $offset");
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->nullKeys .= "$offset:";
            unset($this->storage[$offset]);
            return;
        }

        trigger_error("Undefined index: $offset");
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
}
