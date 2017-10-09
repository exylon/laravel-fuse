<?php


namespace Exylon\Fuse;


use Exylon\Fuse\Support\Attributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

        $this->registerMacros();
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
            Console\RepositoryMakeCommand::class,
        ]);
    }

    /**
     * Register macros
     *
     * @return void
     */
    private function registerMacros()
    {
        Builder::macro('forceMake', function ($attributes) {
            return $this->newModelInstance()->forceFill($attributes);
        });


        $hasGeoip = class_exists('Torann\GeoIP\GeoIP');
        $hasAgent = class_exists('Jenssegers\Agent\Agent');

        if ($hasGeoip) {
            Request::macro('location', function () {
                if ($this->location) {
                    return $this->location;
                }
                return $this->location = new Attributes(geoip($this->getClientIp())->toArray(), [
                    'country_code' => 'iso_code',
                    'latitude'     => 'lat',
                    'longitude'    => 'lon',
                    'zip_code'     => 'postal_code'
                ]);
            });
        }

        if ($hasAgent) {
            Request::macro('agent', function () {
                if ($this->agent) {
                    return $this->agent;
                }
                $agent = new \Jenssegers\Agent\Agent($this->server->all());
                return $this->agent = new Attributes([
                    'agent'      => $agent->getUserAgent(),
                    'is_mobile'  => $agent->isMobile(),
                    'is_phone'   => $agent->isPhone(),
                    'is_tablet'  => $agent->isTablet(),
                    'device'     => $agent->device(),
                    'is_desktop' => $agent->isDesktop(),
                    'platform'   => $agent->platform(),
                    'is_robot'   => $agent->isRobot(),
                    'robot'      => $agent->robot(),
                    'browser'    => $agent->browser(),
                    'languages'  => $agent->languages()
                ]);
            });
        }
    }
}
