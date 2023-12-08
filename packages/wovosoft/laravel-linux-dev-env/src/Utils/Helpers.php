<?php

namespace Wovosoft\LaravelLinuxDevEnv\Utils;

if (!function_exists("ensureTrailingSlash")) {
    function ensureTrailingSlash(?string $string = null): ?string
    {
        if (!$string) {
            return null;
        }

        return str($string)->endsWith("/") ? $string : $string . "/";
    }
}

if (!function_exists('removeTrailingSlash')) {
    function removeTrailingSlash(?string $string = null): ?string
    {
        if (!$string) {
            return null;
        }

        return str($string)->endsWith("/") ? str($string)->beforeLast("/")->value() : $string;
    }
}
