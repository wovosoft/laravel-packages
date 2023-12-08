<?php

namespace Wovosoft\LaravelLinuxDevEnv\Services;

use Closure;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class Postgres
{
    public static function version(): ?string
    {
        $process = Process::run("psql --version");

        if ($process->successful()) {
            return str($process->output())->replace("psql (PostgreSQL) ", "")->trim()->value();
        }

        return null;
    }

    public static function createDatabase(string $database): ProcessResult
    {
        return Process::run(['sudo', '-u', 'postgres', 'psql', '-c', "CREATE DATABASE $database;"]);
    }

    public static function changePassword(string $user, string $password): ProcessResult
    {
        return Process::run(['sudo', '-u', 'postgres', 'psql', '-c', "ALTER USER $user WITH PASSWORD '$password';"]);
    }

    public static function isInstalled(): bool
    {
        return !is_null(self::version());
    }

    /**
     * @description Install PostgreSQL. Should be run from the terminal.
     * @param Closure|null $closure
     * @return ProcessResult
     */
    public static function install(?Closure $closure = null): ProcessResult
    {
        return Process::path(__DIR__ . "/../../scripts/postgresql/")
            ->run(["bash", "install.sh"], function ($type, $buffer) use ($closure) {
                if (is_callable($closure)) {
                    $closure($type, $buffer);
                }
            });
    }

    public static function uninstall(?Closure $closure = null): ProcessResult
    {
        return Process::path(__DIR__ . "/../../scripts/postgresql/")
            ->run(["bash", "uninstall.sh"], function ($type, $buffer) use ($closure) {
                if (is_callable($closure)) {
                    $closure($type, $buffer);
                }
            });
    }

    public static function status(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'postgresql', 'status'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function start(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'postgresql', 'start'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function stop(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'postgresql', 'stop'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function restart(?Closure $closure = null): ProcessResult
    {
        return Process::run(['service', 'postgresql', 'restart'], function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
    }

    public static function isActive(): bool
    {
        $status = "";
        $process = self::status(function ($type, $buffer) use (&$status) {
            $status .= $buffer;
        });

        return $process->successful()
            && str($status)->contains("Active: active")
            && str($status)->contains("postgresql");
    }
}
