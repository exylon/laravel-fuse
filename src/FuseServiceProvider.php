<?php


namespace Exylon\Fuse;


use Illuminate\Support\ServiceProvider;

class FuseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerArtisanCommands();
        }
    }

    /**
     * Register Fuse Commands
     *
     * @return void
     */
    protected function registerArtisanCommands()
    {
        $this->commands([
            Console\SubscriberMakeCommand::class,
            Console\ServiceMakeCommand::class,
        ]);
    }
}
