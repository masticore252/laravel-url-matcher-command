<?php
namespace Masticore\LaravelUrlTestMatcher;

use Illuminate\Support\ServiceProvider;

class UrlMatcherServiceProvider extends ServiceProvider
{

    public function boot(Router $router)
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                UrlMatcherCommand::class
            ]);
        }
    }

}
