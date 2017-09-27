<?php
/**
 * Estende as funcionalidades do ArrayObject com as informações disponíveis no metadata
 *
 * Ele deveria extender Realejo\Stdlib\ArrayObject mas como rescreve a maioria dos
 * metodos eu deixei numa classe a parte
 */
namespace Realejo\Service\Metadata;

use \Realejo\Stdlib\ArrayObject as StdlibArrayObject;

class ArrayObject extends StdlibArrayObject
{
    /**
     * @var MetadataArrayObject
     */
    protected $metadata;

    protected $metadataKeyName = 'metadata';

    /**
     * @return MetadataArrayObject
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = new MetadataArrayObject();
        }
        return $this->metadata;
    }

    /**
     * @param array|MetadataArrayObject $metadata
     * @return $this
     */
    public function setMetadata($metadata)
    {
        if (is_array($metadata)) {
            $metadata = new MetadataArrayObject($metadata);
        }

        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @param array $metadata
     * @return $this
     */
    public function addMetadata($metadata)
    {
        $this->getMetadata()->addMetadata($metadata);

        return $this;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function hasMetadata($key)
    {
        return $this->getMetadata()->offsetExists($key);
    }

    public function populate(array $data)
    {
        if (isset($data[$this->metadatakeyName])) {
            if (is_string($data[$this->metadatakeyName])) {
                $data[$this->metadatakeyName] = json_decode($data[$this->metadatakeyName], true);
            }
            if (! empty($data[$this->metadatakeyName])) {
                $this->setMetadata($data[$this->metadatakeyName]);
            }
            unset($data[$this->metadatakeyName]);
        }

        parent::populate($data);
    }

    /**
     * @param bool $unMapKeys
     * @return array
     */
    public function toArray($unMapKeys = true)
    {
        $toArray = parent::toArray($unMapKeys);
        if (! empty($this->getMetadata()->count())) {
            $toArray[$this->metadatakeyName] = $this->getMetadata()->toArray();
        }

        return $toArray;
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $offset = $this->getMappedKey($offset);
        if (parent::offsetExists($offset)) {
            return true;
        }

        return $this->hasMetadata($offset);
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $offset = $this->getMappedKey($offset);

        if (parent::offsetExists($offset)) {
            return parent::offsetGet($offset);
        }

        if ($this->hasMetadata($offset)) {
            return $this->getMetadata()->offsetGet($offset);
        }

        trigger_error("Undefined index: $offset");
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->getMappedKey($offset, true);

        if (parent::offsetExists($offset)) {
            parent::offsetSet($offset, $value);
            return;
        }

        if ($this->hasMetadata($offset)) {
            $this->getMetadata()->offsetSet($offset, $value);
            return;
        }

        // Verifica as chaves estão bloqueadas
        //@todo tem que testar isso!!
        if (! $this->getLockedKeys()) {
            $this->storage[$offset] = $value;
            return;
        }

        trigger_error("Undefined index: $offset");
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (parent::offsetExists($offset)) {
            parent::offsetUnset($offset);
            return;
        }

        if ($this->hasMetadata($offset)) {
            $this->getMetadata()->offsetUnset($offset);
            return;
        }

        throw new \Exception("You cannot remove a property");
    }
}
