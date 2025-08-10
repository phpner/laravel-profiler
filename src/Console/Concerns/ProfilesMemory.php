<?php

namespace Phpner\MemoryProfiler\Console\Concerns;

use Phpner\MemoryProfiler\MemoryProfiler;

trait ProfilesMemory
{
    /**
     * @param callable $callback
     * @return int
     */
    public function withMemoryProfiling(callable $callback): int
    {
        /** @var MemoryProfiler $profiler */
        $profiler = app(MemoryProfiler::class);

        if (!config('laravel-memory-profiler.enabled')) {
            return (int) $callback();
        }

        $ctx = $profiler->start('artisan');
        $code = (int) $callback();
        $profiler->stop($ctx, [
            'command'=> $this->getName(),
            'exit'   => $code,
        ]);

        return $code;
    }
}
