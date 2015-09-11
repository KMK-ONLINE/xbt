<?php

namespace Lib\xbt;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Config;
use View;

class LaravelServiceProvider extends ServiceProvider {

    public function register()
    {
        $app = $this->app;

        $resolver = $app['view.engine.resolver'];

        $app->bindShared('xbt.compiler', function($app) {
            $cachePath = $app['config']['view.compiled'];
            $classPath = $app['config']['view.xbt_cache'];
            return new LaravelCompiler($app['files'], $cachePath, $classPath, Config::get('view')['paths']);
        });

        $resolver->register('xbt.engine', function() use($app) {
            return new CompilerEngine($app['xbt.compiler'], $app['files']);
        });

        View::addExtension('xbt.php', 'xbt.engine');
    }

}
