<?php

namespace Phpner\MemoryProfiler\Middleware;

use Closure;
use Illuminate\Http\Request;
use Phpner\MemoryProfiler\MemoryProfiler;

class HttpProfiler
{
    /**
     * Create a new middleware instance.
     *
     * @param MemoryProfiler $profiler The profiler service instance.
     */
    public function __construct(private readonly MemoryProfiler $profiler)
    {
    }

    /**
     * Handle an incoming HTTP request and measure its execution time and memory usage.
     *
     * This middleware:
     * - Starts measurement at the beginning of the request.
     * - Passes the request to the next middleware/controller.
     * - Stops measurement after the response is generated.
     * - Logs or sends results to Laravel Telescope, depending on configuration.
     * - Optionally adds `X-Memory-Peak` and `X-Duration-ms` headers to the response.
     *
     * @param Request $request The incoming request.
     * @param Closure $next The next middleware or request handler.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If profiler is disabled in config, skip profiling.
        if (!config('laravel-memory-profiler.enabled')) {
            return $next($request);
        }

        // Start profiling.
        $ctx = $this->profiler->start('html');

        // Handle request.
        $response = $next($request);

        // Stop profiling and collect results.
        $data = $this->profiler->stop($ctx, [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'route' => optional($request->route())->getName(),
            'status' => $response->getStatusCode(),
        ]);

        // Optionally add results to HTTP response headers.
        if (config('laravel-memory-profiler.response_headers')) {
            $response->headers->set('X-Memory-Peak', (string)$data['memory_peak']);
            $response->headers->set('X-Duration-ms', (string)$data['duration_ms']);
        }

        return $response;
    }
}
