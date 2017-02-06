<?php
namespace Realejo\Metadata;

interface ArrayObjectInterface
{
    /**
     * @param array $metadata
     */
    public function setMetadata($metadata);

    /**
     * @param array $metadata
     */
    public function addMetadata($metadata);

    /**
     * @return \stdClass
     */
    public function getMetadata();
}
