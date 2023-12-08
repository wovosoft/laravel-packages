<?php

namespace Wovosoft\LaravelLinuxDevEnv\Services;

use Closure;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class Php
{
    public static function install(?Closure $closure = null): ProcessResult
    {
        return Process::run([
            "sudo add-apt-repository ppa:ondrej/php",
            "sudo apt update",
            "apt install php -y"
        ], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function fullVersion(): string
    {
        $output = "";
        Process::run("php -v", function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });
        return $output;
    }

    public static function majorAndMinorComboVersion(): ?string
    {
        $pattern = '/PHP (\d+\.\d+)/';
        preg_match($pattern, self::fullVersion(), $matches);

        // Display the extracted version number
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    public static function version(): ?string
    {
        // Use regular expression to extract the version number
        $pattern = '/PHP (\d+\.\d+\.\d+)/';
        preg_match($pattern, self::fullVersion(), $matches);

        // Display the extracted version number
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    public static function isVersionInstalled(string $version): bool
    {
        $output = "";
        Process::run("php -v", function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });
        return str_contains($output, "PHP $version");
    }

    public static function isInstalled(): bool
    {
        $output = "";
        Process::run("php -v", function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        return str_contains($output, "PHP");
    }
}
