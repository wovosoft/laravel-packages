<?php

namespace Wovosoft\LaravelLinuxDevEnv\Services;

use Closure;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class Nginx
{
    public static function start(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'nginx', 'start'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function stop(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'nginx', 'stop'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function restart(?Closure $closure = null): ProcessResult
    {
        return Process::run(["service", "nginx", "restart"], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });

    }

    public static function status(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'nginx', 'status'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function install(?Closure $closure = null): ProcessResult
    {
        return Process::run(['apt-get', 'install', 'nginx'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }


    /**
     * @throws \Throwable
     */
    public static function isInstalled(): bool
    {
        $output = '';
        $process = Process::run(['nginx', '-v'], function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        $process->throwIf($process->failed());

        return str($output)->contains("nginx version");
    }

    public static function version(): string
    {
        $output = '';
        Process::run(['nginx', '-v'], function ($type, $buffer) use (&$output) {
            $output .= $buffer;
        });

        return str($output)->replace("nginx version: nginx/", "")->trim()->value();
    }
}
