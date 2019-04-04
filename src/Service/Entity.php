<?php

namespace Realejo\Service;

use InvalidArgumentException;

class Entity
{
    protected $schema = [];

    protected const TYPE_STRING = 'string';
    protected const TYPE_INTEGER = 'integer';
    protected const TYPE_FLOAT = 'float';
    protected const TYPE_DATE = 'date';
    protected const TYPE_DATETIME = 'datetime';
    protected const TYPE_BOOLEAN = 'boolean';

    public function __construct($data = null)
    {
        if ($data === null) {
            $data = [];
        } elseif (!is_array($data)) {
            throw new InvalidArgumentException('$data must be an array');
        }
        $this->populate($data);
    }

    public function populate(array $data = [])
    {
        foreach ($this->schema as $key => $definition) {
            $this->setValue($key, $data[$key] ?? null);
        }
    }

    public function __set($name, $value): self
    {
        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->schema) && isset($this->$name)) {
            return $this->$name;
        }
    }

    private function setValue(string $key, $value)
    {
        $config = $this->schema[$key];
        $name = $this->getName($key);
        $this->$name = $value;
    }

    private function getName(string $key): ?string
    {
        if (isset($this->schema[$key])) {
            if (isset($this->schema[$key]['alias'])) {
                return $this->schema[$key]['alias'];
            }
            return $key;
        }
        return null;
    }

}