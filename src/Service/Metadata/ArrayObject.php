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
        if (isset($data['metadata'])) {
            if (is_string($data['metadata'])) {
                $data['metadata'] = json_decode($data['metadata'], true);
            }
            if (! empty($data['metadata'])) {
                $this->setMetadata($data['metadata']);
            }
            unset($data['metadata']);
        }

        parent::populate($data);
    }

    public function toArray()
    {
        $toArray = parent::toArray();
        if (! empty($this->getMetadata()->count())) {
            $toArray['metadata'] = $this->getMetadata()->toArray();
        }

        return $toArray;
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $offset = $this->getDeprecatedKey($offset);
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
        $offset = $this->getDeprecatedKey($offset);

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
        $offset = $this->getDeprecatedKey($offset, true);

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
