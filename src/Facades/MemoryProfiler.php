<?php

namespace Phpner\MemoryProfiler\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array start(string $type = '')
 * @method static array stop(array $ctx, array $extra = [])
 * @method static void  dispatch(array $data)
 * @see MemoryProfilerClass
 */
class MemoryProfiler extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ov.memory_profiler';
    }
}
