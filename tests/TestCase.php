<?php

namespace Phpner\MemoryProfiler\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Phpner\MemoryProfiler\MemoryProfilerServiceProvider;

abstract class TestCase extends BaseTestCase
{

    protected function packagePath(string $path = ''): string
    {
        $base = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
        return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
    }

    protected function getPackageProviders($app): array
    {
       // return [MemoryProfilerServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        // Конфиг пакета
        $app['config']->set('laravel-memory-profiler.enabled', true);
        $app['config']->set('laravel-memory-profiler.driver', 'log');
        $app['config']->set('laravel-memory-profiler.threshold', 0);
        $app['config']->set('laravel-memory-profiler.response_headers', true);

        // Хранилище и лог-канал внутри пакета (а не в vendor/)
        $app->useStoragePath($this->packagePath('storage'));

        $app['config']->set('logging.channels.memory_profiler', [
            'driver' => 'single',
            'path'   => $this->memoryLogPath(),
            'level'  => 'debug',
            'replace_placeholders' => true,
        ]);

        // Если ваш код пишет в дефолтный Log, направим его сюда
        $app['config']->set('logging.default', 'memory_profiler');
    }

    protected function memoryLogPath(): string
    {
        return $this->packagePath('storage/logs/memory_profiler.log');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Готовим директории/файл, чтобы Monolog не падал
        @mkdir($this->packagePath('storage/logs'), 0777, true);

        $log = $this->memoryLogPath();
        if (file_exists($log)) {
            @unlink($log);
        }
    }
}
