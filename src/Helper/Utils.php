<?php

namespace hubipe\HuQrPayment\Helper;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

final class Utils
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static $constants = [];

    /**
     * @param mixed $variable
     *
     * @return string
     */
    public static function getType($variable): string
    {
        if (is_object($variable)) {
            return get_class($variable);
        } elseif (is_resource($variable)) {
            return 'resource (' . get_resource_type($variable) . ')';
        } else {
            return gettype($variable);
        }
    }

    /**
     * @param string $class
     *
     * @throws ReflectionException
     *
     * @return array<string, mixed>
     */
    public static function getConstants(string $class)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("The class '{$class}' is not defined");
        }

        if (!isset(static::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            static::$constants[$class] = $reflection->getConstants();
        }

        return static::$constants[$class];
    }

}
