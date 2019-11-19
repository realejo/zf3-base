<?php

namespace Realejo\Enum;

use InvalidArgumentException;

/**
 * Enum class
 *
 * You can add a name/description por each constant to use in forms
 *
 * static protected $constDescription = [
 *  self::CONST => [name, description],
 *  self::CONST => name
 * ];
 *
 * The const should be a numeric ou string.
 *
 * integer const IS NOT considered string. Ex: '666' !== 666
 *
 * It fails if the const is NULL or BOOLEAN
 *
 * @link      https://github.com/realejo/zf3-base
 * @copyright Copyright (c) 2018 Realejo (https://realejo.com.br)
 */
abstract class Enum
{
    protected static $constDescription = [];

    protected $value;

    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    public function setValue($value = null): void
    {
        if ($value !== null && !static::isValid($value)) {
            throw new InvalidArgumentException("Value '$value' is not valid.");
        }

        $this->value = $value;
    }

    /**
     * Return the const values with it's names
     *
     * @return array
     */
    public static function getNames(): array
    {
        $fetchAll = [];
        foreach (static::$constDescription as $v => $description) {
            $fetchAll[$v] = (is_array($description)) ? $description[0] : $description;
        }
        return $fetchAll;
    }

    /**
     * Returns descriptions for the constants
     *
     * @return array
     */
    public static function getDescriptions(): array
    {
        $getDescriptions = [];
        foreach (static::$constDescription as $v => $description) {
            $getDescriptions[$v] = (is_array($description)) ? $description[1] : $description;
        }
        return $getDescriptions;
    }

    /**
     * Return the const values
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_keys(static::getNames());
    }

    /**
     * Return the name os the constant
     *
     * @param null $value
     *
     * @return string|null
     */
    public static function getName($value = null)
    {
        $names = self::getNames();

        if (in_array($value, array_keys($names), true)) {
            return $names[$value];
        }

        return null;
    }

    /**
     * Return the name os the constant
     *
     * @param null $value
     *
     * @return string|array
     */
    public function getValueName($value = null)
    {
        if ($value === null && $this->value !== null) {
            $value = $this->value;
        }

        return self::getName($value);
    }

    /**
     * Return the name os the constant
     *
     * @param null $value
     *
     * @return string|array
     */
    public function getValueDescription($value = null)
    {
        if ($value === null && $this->value !== null) {
            $value = $this->value;
        }

        return self::getDescription($value);
    }

    /**
     * Descrição dos status
     *
     * @param null $value
     *
     * @return string|null
     */
    public static function getDescription($value = null)
    {
        $descriptions = self::getDescriptions();

        if (in_array($value, array_keys($descriptions), true)) {
            return $descriptions[$value];
        }

        return null;
    }

    public static function isValid($value): bool
    {
        $const = static::getNames();
        return in_array($value, array_keys($const), true);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function is($value): bool
    {
        return ($value === $this->value);
    }
}
