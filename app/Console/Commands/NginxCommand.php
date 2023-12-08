<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wovosoft\LaravelLinuxDevEnv\Services\Nginx;


class NginxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev-env:nginx
            {--start}
            {--restart}
            {--stop}
            {--restart}
            {--status}
            {--install}
            {--is-installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage NGINX service in LINUX Environment';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start = $this->option('start');
        $stop = $this->option('stop');
        $restart = $this->option('restart');
        $status = $this->option('status');
        $isInstalled = $this->option('is-installed');
        $install = $this->option('install');

        //only one option can be specified
        //so using an if-else-if-else chain
        if ($start) {
            $this->start();
        } elseif ($stop) {
            $this->stop();
        } elseif ($restart) {
            $this->restart();
        } elseif ($status) {
            $this->status();
        } elseif ($isInstalled) {
            $this->isInstalled();
        } elseif ($install) {
            $this->install();
        } else {
            $this->info('Please specify an option');
        }
    }

    protected function start(): void
    {
        $this->info('Starting NGINX service...');
        $output = Nginx::start(fn($type, $buffer) => $this->output->write($buffer));

        if ($output->successful()) {
            $this->info('NGINX service started successfully');
        } else {
            $this->error($output->errorOutput());
        }
    }

    protected function stop(): void
    {
        $this->info('Stopping NGINX service...');
        $output = Nginx::stop(fn($type, $buffer) => $this->output->write($buffer));

        if ($output->successful()) {
            $this->info('NGINX service stopped successfully');
        } else {
            $this->error($output->errorOutput());
        }
    }

    protected function restart(): void
    {
        $this->info('Restarting NGINX service...');
        $output = Nginx::restart(fn($type, $buffer) => $this->output->write($buffer));

        if ($output->successful()) {
            $this->info('NGINX service restarted successfully');
        } else {
            $this->error($output->errorOutput());
        }
    }

    protected function status(): void
    {
        $this->info('Checking NGINX service status...');

        Nginx::status(fn($type, $buffer) => $this->output->write($buffer));
    }

    /**
     * @throws \Throwable
     */
    protected function install(): void
    {
        if (Nginx::isInstalled()) {
            $this->info('NGINX service is already installed');
            return;
        }

        $this->info('Installing NGINX service...');

        $output = Nginx::install(fn($type, $buffer) => $this->output->write($buffer));

        if ($output->successful()) {
            $this->info('NGINX service installed successfully');
        } else {
            $this->error($output->errorOutput());
        }
    }

    /**
     * @throws \Throwable
     */
    protected function isInstalled(): void
    {
        $this->info('Checking if NGINX service is installed...');

        if (Nginx::isInstalled()) {
            $this->info('NGINX service is installed');
            $this->info("Version : " . Nginx::version());
        } else {
            $this->info('NGINX service is not installed');
        }
    }
}
