<?php

namespace App\Support;

final class Money
{
    public static function add(string $left, string $right): string
    {
        if (function_exists('bcadd')) {
            return bcadd($left, $right, 2);
        }

        return number_format(((float) $left) + ((float) $right), 2, '.', '');
    }

    public static function sub(string $left, string $right): string
    {
        if (function_exists('bcsub')) {
            return bcsub($left, $right, 2);
        }

        return number_format(((float) $left) - ((float) $right), 2, '.', '');
    }

    public static function mul(string $left, string $right): string
    {
        if (function_exists('bcmul')) {
            return bcmul($left, $right, 2);
        }

        return number_format(((float) $left) * ((float) $right), 2, '.', '');
    }
}
