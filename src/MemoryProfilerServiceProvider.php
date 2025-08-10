<?php

namespace Phpner\MemoryProfiler;

use Illuminate\Support\ServiceProvider;

class MemoryProfilerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-memory-profiler.php', 'laravel-memory-profiler');

        // Bind for Facade
        $this->app->singleton('ov.memory_profiler', fn () => new MemoryProfiler(
            threshold: (int) config('laravel-memory-profiler.threshold', 0),
            driver: (string) config('laravel-memory-profiler.driver', 'log'),
        ));
        $this->app->alias('ov.memory_profiler', MemoryProfiler::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-memory-profiler.php' => config_path('laravel-memory-profiler.php'),
        ], 'config');
    }
}
