<?php
namespace Xbt;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Config;
use View;

class LaravelServiceProvider extends ServiceProvider {

    public function register()
    {
        $app = $this->app;

        $resolver = $app['view.engine.resolver'];

        $app->singleton('xbt.compiler', function($app) {
            $cachePath = $app['config']['view.compiled'];
            $classPath = $app['config']['view.xbt_cache'];
            return new LaravelCompiler($app['files'], $cachePath, $classPath, $app['config']['view.paths']);
        });

        $resolver->register('xbt.engine', function() use($app) {
            return new CompilerEngine($app['xbt.compiler'], $app['files']);
        });

        View::addExtension('xbt.php', 'xbt.engine');

        $this->commands(ViewCompile::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/view.php', 'view'
        );
    }

}
