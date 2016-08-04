<?php namespace Luminaire\Premailer\Laravel;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     04/08/2016
 * @time     11:15
 */

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\CompilerEngine;
use Luminaire\Premailer\Laravel\PremailerEngine;

/**
 * The "Premailer" service provider
 *
 * @package  \Luminaire\Premailer\Laravel
 */
class PremailerServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('premailer.compiler', function ($app)
        {
            return new PremailerCompiler($app['files'], storage_path('premailer'));
        });

        $this->app->singleton('premailer.engine', function ($app)
        {
            $blade = new CompilerEngine($app['blade.compiler']);

            return new PremailerEngine($blade, $app['premailer.compiler']);
        });
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $view = $this->app['view'];

        $view->addExtension('premailer', 'premailer', function ()
        {
            return $this->app['premailer.engine'];
        });
    }

}
