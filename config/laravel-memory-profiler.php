<?php

return [
    'enabled' => env('MEMORY_PROFILER_ENABLED', true),
    'driver' => env('MEMORY_PROFILER_DRIVER', 'log'), // log | telescope | both
    'threshold' => env('MEMORY_PROFILER_THRESHOLD', 0),
    'response_headers' => env('MEMORY_PROFILER_HEADERS', true),
];
