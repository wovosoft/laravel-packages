<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wovosoft\LaravelLinuxDevEnv\Services\Php;


class PhpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev-env:php
            {--start}
            {--restart}
            {--stop}
            {--restart}
            {--install}
            {--php-version}
            {--is-installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start = $this->option('start');
        $stop = $this->option('stop');
        $restart = $this->option('restart');
        $isInstalled = $this->option('is-installed');
        $install = $this->option('install');
        $version = $this->option('php-version');

        //only one option can be specified
        //so using an if-else-if-else chain
        if ($start) {
            $this->start();
        } elseif ($stop) {
            $this->stop();
        } elseif ($restart) {
            $this->restart();
        } elseif ($isInstalled) {
            $this->isInstalled();
        } elseif ($install) {
            $this->install();
        } elseif ($version) {
            $this->version();
        } else {
            $this->info('Please specify an option');
        }
    }

    private function start()
    {

    }

    private function stop()
    {

    }

    private function restart()
    {

    }

    private function version(): void
    {
        $this->info(Php::version());
    }




    private function isInstalled(): void
    {
        $this->info(Php::isInstalled() ? "PHP is installed" : "PHP is not installed");
    }

    private function install()
    {

    }
}
