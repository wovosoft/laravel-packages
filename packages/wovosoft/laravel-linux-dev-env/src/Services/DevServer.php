<?php

namespace Wovosoft\LaravelLinuxDevEnv\Services;

use Closure;
use Illuminate\Support\Facades\File;
use Throwable;
use Wovosoft\LaravelLinuxDevEnv\Utils\Symlink;
use function Wovosoft\LaravelLinuxDevEnv\Utils\ensureTrailingSlash;

class DevServer
{
    private string $nginxConfigStubPath = __DIR__ . "/../../stubs/nginx.conf";

    private string  $phpFpmVersion;
    private string  $phpVersion;
    private string  $serverName;
    private string  $rootPath;
    private string  $phpFpmSocket;
    private string  $nginxConfigDirectory            = "/etc/nginx/sites-available/default";
    private string  $nginxConfigPathEnabledDirectory = "/etc/nginx/sites-enabled/default";
    private string  $vHostsPath                      = "/etc/hosts";
    private string  $ip                              = "127.0.0.1";
    private ?string $nginxConfig                     = null;

    public function __construct()
    {
        $this->phpFpmVersion = Php::majorAndMinorComboVersion();
        $this->phpVersion = Php::version();
    }

    public static function make(): static
    {
        return new static();
    }


    public function setNginxConfig(
        string  $serverName,
        string  $rootPath,
        ?string $phpFpmSocket = null,
        string  $configDirectory = "/etc/nginx/sites-available",
        string  $configEnabledDirectory = "/etc/nginx/sites-enabled",
        string  $vHostsPath = "/etc/hosts",
        string  $ip = "127.0.0.1",
    ): static
    {
        return $this
            ->setServerName($serverName)
            ->setRootPath($rootPath)
            ->setNginxConfigDirectory($configDirectory)
            ->setNginxConfigEnabledDirectory($configEnabledDirectory)
            ->setVHostsPath($vHostsPath)
            ->setIp($ip)
            ->setPhpFpmSocket($phpFpmSocket ?: "unix:/var/run/php/php-$this->phpFpmVersion-fpm.sock");
    }

    /**
     * @throws \Exception
     */
    public function createNginxConfig(): static
    {
        $this->nginxConfig = str($this->getNginxConfigStub())
            ->replace("{{SERVERNAME}}", $this->getServerName())
            ->replace("{{ROOTPATH}}", $this->getRootPath())
            ->replace("{{PHPFPMSOCKET}}", $this->getPhpFpmSocket())
            ->value();
        return $this;
    }

    public function removeNginxConfig(): static
    {
        if (File::exists($this->getNginxConfigPath())) {
            File::delete($this->getNginxConfigPath());
        }
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getNginxConfig(): ?string
    {
        if (!$this->nginxConfig) {
            $this->createNginxConfig();
        }
        return $this->nginxConfig;
    }

    /**
     * @throws \Exception
     */
    public function writeNginxConfig(): static
    {
        File::ensureDirectoryExists($this->getNginxConfigDirectory(), 0777);

        File::put(
            $this->getNginxConfigPath(),
            $this->getNginxConfig()
        );

        return $this;
    }

    /**
     * Writes the server name to the /etc/hosts file
     * @throws \Exception
     * @throws Throwable
     */
    public function writeHostRecord(?Closure $closure = null): static
    {

        File::ensureDirectoryExists(dirname($this->getVHostsPath()), 0777);

        $backupPath = $this->getVHostsPath() . "-" . now()->timestamp . ".bak";

        if (File::exists($this->getVHostsPath())) {
            File::copy($this->getVHostsPath(), $backupPath);
        }

        try {
            $hostContent = File::exists($this->getVHostsPath()) ? File::get($this->getVHostsPath()) : "";

            if (!str($hostContent)->contains($this->getServerName())) {
                $hostContent .= "\n{$this->getIp()}\t{$this->getServerName()}";

                File::put($this->getVHostsPath(), $hostContent);
            }

            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            if (is_callable($closure)) {
                $closure("info", "Host record written successfully at [{$this->getVHostsPath()}]]");
            }
        } catch (Throwable $throwable) {
            if (File::exists($backupPath)) {
                File::move($backupPath, $this->getVHostsPath());
            }

            if (is_callable($closure)) {
                $closure("error", $throwable->getMessage());
            }

            throw $throwable;
        }
        return $this;
    }

    public function removeHostRecord(?Closure $closure = null): static
    {
        if (!File::exists($this->getVHostsPath())) {
            return $this;
        }

        $backupPath = $this->getVHostsPath() . "-" . now()->timestamp . ".bak";
        File::copy($this->getVHostsPath(), $backupPath);

        try {
            $hostContent = File::get($this->getVHostsPath());
            $hostContent = str($hostContent)->replace("\n{$this->getIp()}\t{$this->getServerName()}", "");
            File::put($this->getVHostsPath(), $hostContent);
            File::delete($backupPath);

            if (is_callable($closure)) {
                $closure("info", "Host record removed successfully from [{$this->getVHostsPath()}]]");
            }
        } catch (Throwable $throwable) {
            File::delete($backupPath);

            if (is_callable($closure)) {
                $closure("error", $throwable->getMessage());
            }
        }

        return $this;
    }

    public function restartNginx(Closure $closure = null): static
    {
        Nginx::restart(function ($type, $buffer) use ($closure) {
            if (is_callable($closure)) {
                $closure($type, $buffer);
            }
        });
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function enableNginxConfig(): static
    {
        File::ensureDirectoryExists($this->getNginxConfigPathEnabledDirectory(), 0777);

        try {
            Symlink::create(
                target: $this->getNginxConfigPath(),
                link  : $this->getNginxConfigEnabledPath()
            );
        } catch (Throwable $throwable) {
            echo $throwable->getMessage();
        }

        return $this;
    }

    /**
     * Removes the symlink from sites-enabled
     * @return $this
     * @throws \Exception
     */
    public function disableNginxConfig(): static
    {
        Symlink::removeLink($this->getNginxConfigEnabledPath());

        return $this;
    }

    public function getVHostsPath(): string
    {
        return $this->vHostsPath;
    }

    public function setVHostsPath(string $path): static
    {
        $this->vHostsPath = $path;
        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function getNginxConfigPath(): string
    {
        return ensureTrailingSlash($this->getNginxConfigDirectory()) . $this->getServerName() . ".conf";
    }

    public function getNginxConfigEnabledPath(): string
    {
        return ensureTrailingSlash($this->getNginxConfigPathEnabledDirectory()) . $this->getServerName() . ".conf";
    }


    /**
     * Get the contents of the nginx config stub.
     * @return string
     * @throws \Exception
     */
    public function getNginxConfigStub(): string
    {
        return File::get($this->nginxConfigStubPath);
    }

    public function setNginxConfigStubPath(string $path): static
    {
        $this->nginxConfigStubPath = $path;
        return $this;
    }

    public function getPhpFpmVersion(): string
    {
        return $this->phpVersion;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getServerName(): string
    {
        return $this->serverName;
    }

    public function setServerName(string $serverName): static
    {
        $this->serverName = $serverName;
        return $this;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function setRootPath(string $rootPath): static
    {
        $this->rootPath = $rootPath;
        return $this;
    }

    public function getPhpFpmSocket(): string
    {
        return $this->phpFpmSocket;
    }

    public function setPhpFpmSocket(string $phpFpmSocket): static
    {
        $this->phpFpmSocket = $phpFpmSocket;
        return $this;
    }

    public function getNginxConfigDirectory(): string
    {
        return $this->nginxConfigDirectory;
    }

    public function setNginxConfigDirectory(string $directory): static
    {
        $this->nginxConfigDirectory = $directory;
        return $this;
    }


    public function getNginxConfigPathEnabledDirectory(): string
    {
        return $this->nginxConfigPathEnabledDirectory;
    }

    public function setNginxConfigEnabledDirectory(string $directory): static
    {
        $this->nginxConfigPathEnabledDirectory = $directory;
        return $this;
    }
}
