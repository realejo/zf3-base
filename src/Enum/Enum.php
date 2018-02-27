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
 * @link      http://bitbucket.org/bffc-bobs/bobs-fa
 * @copyright Copyright (c) 2018 Realejo (http://realejo.com.br)
 * @license   proprietary
 */
abstract class Enum
{
    protected static $constDescription = [];

    protected static $value;

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
     * Return the name os the constant
     *
     * @param null $value
     * @return string
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

    public function __construct($value = null)
    {
        if ($value !== null && !$this->isValid($value)) {
            throw new InvalidArgumentException("Value '$value' is not valid.");
        }

        self::$value = $value;
    }

    public function getValue()
    {
        return self::$value;
    }

    public function is($value): bool
    {
        return ($value === self::$value);
    }
}
