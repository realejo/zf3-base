<?php
namespace Realejo\Db;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Predicate;

/**
 * @method DeleteWithLimit where($predicate, $combination = Predicate\PredicateSet::OP_AND)
 */
class DeleteWithLimit extends Delete
{
    const SPECIFICATION_LIMIT = 'limit';

    protected $specifications = [
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s',
        self::SPECIFICATION_LIMIT => 'LIMIT %1$s',
    ];

    /**
     * @var string
     */
    protected $processInfo = ['paramPrefix' => '', 'subselectCount' => 0];

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @param int $limit
     * @return self Provides a fluent interface
     * @throws \InvalidArgumentException
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects parameter to be numeric, "%s" given',
                __METHOD__,
                (is_object($limit) ? get_class($limit) : gettype($limit))
            ));
        }

        $this->limit = $limit;
        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where,
            'limit' => $this->limit,
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processLimit(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $parameterContainer->offsetSet('limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('limit')];
        }
        return [$platform->quoteValue($this->limit)];
    }
}
