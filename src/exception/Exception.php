<?php

namespace oihub\exception;

class Exception
{
    public static function error(string $message, int $code = 0)
    {
        throw new \Exception($message, $code);
    }

    public static function badMethodCall(string $message, int $code = 0)
    {
        throw new BadMethodCallException($message, $code);
    }

    public static function badRequest(string $message, int $code = 0)
    {
        throw new BadRequestException($message, $code);
    }

    public static function invalidArgument(string $message, int $code = 0)
    {
        throw new InvalidArgumentException($message, $code);
    }

    public static function logic(string $message, int $code = 0)
    {
        throw new LogicException($message, $code);
    }

    public static function overrideService(string $name)
    {
        throw new OverrideServiceException($name);
    }

    public static function permission(string $name)
    {
        throw new PermissionException($name);
    }

    public static function readOnlyProperty(string $name)
    {
        throw new ReadOnlyPropertyException($name);
    }

    public static function runtime(string $message, int $code = 0)
    {
        throw new RuntimeException($message, $code);
    }

    public static function unknownMethod(string $name)
    {
        throw new UnknownMethodException($name);
    }

    public static function unknownProperty(string $name)
    {
        throw new UnknownPropertyException($name);
    }

    public static function writeOnlyProperty(string $name)
    {
        throw new WriteOnlyPropertyException($name);
    }
}
