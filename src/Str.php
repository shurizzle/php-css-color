<?php

namespace Shura\CSS\Color;

class Str
{
    private static $snakeCache = [];
    protected static $camelCache = [];
    protected static $studlyCache = [];

    public static function snake($value)
    {
        $key = $value;
        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);

            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $value), 'UTF-8');
        }

        return static::$snakeCache[$key] = $value;
    }

    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
