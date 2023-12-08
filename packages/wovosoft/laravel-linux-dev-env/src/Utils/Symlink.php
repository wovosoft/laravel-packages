<?php

namespace Wovosoft\LaravelLinuxDevEnv\Utils;

use Exception;
use File;

/**
 * @see \Illuminate\Foundation\Console\StorageLinkCommand
 */
class Symlink
{
    public function __construct(
        private readonly string $target,
        private readonly string $link
    )
    {
    }

    /**
     * @throws Exception
     */
    public static function create(
        string $target,
        string $link
    ): bool
    {
        if (is_link($link)) {
            throw new Exception("The [$link] link already exists.");
        }

        if (!file_exists($target)) {
            throw new Exception("The [$target] target does not exist.");
        }

        $instance = new static($target, $link);

        return $instance->createLink();
    }


    /**
     * @throws Exception
     */
    public function createLink(): bool
    {
        try {
            File::link($this->target, $this->link);
            return true;
        } catch (Exception $e) {
            throw new Exception("The [$this->link] link could not be created.");
        }
    }

    public static function removeLink(string $link): bool
    {
        if (is_link($link)) {
            return unlink($link);
        }
        return false;
    }
}
