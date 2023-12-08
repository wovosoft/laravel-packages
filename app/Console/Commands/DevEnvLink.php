<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wovosoft\LaravelLinuxDevEnv\Services\DevServer;
use function Wovosoft\LaravelLinuxDevEnv\Utils\removeTrailingSlash;

class DevEnvLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev-env:link
                {path}
                {--tld=test}
                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a symbolic link to the specified path in the dev server as local website';

    /**
     * Execute the console command.
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $path = removeTrailingSlash($this->argument('path'));
        $serverName = basename($path);
        $tld = $this->option('tld');

        DevServer::make()
            ->setNginxConfig(
                serverName            : $serverName . "." . $tld,
                rootPath              : $path,
//                configDirectory       : storage_path("sites-available"),
//                configEnabledDirectory: storage_path("sites-enabled"),
//                vHostsPath            : storage_path("hosts")
            )
            ->writeNginxConfig()
            ->enableNginxConfig()
            ->writeHostRecord()
            ->restartNginx(function ($type, $buffer) {
                $this->output->write($buffer);
            });
    }
}
