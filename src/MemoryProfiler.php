<?php

namespace Phpner\MemoryProfiler;

use Illuminate\Support\Facades\Log;

class MemoryProfiler
{

    public function __construct(
        /**
         * Minimum peak memory threshold (in bytes) required for logging.
         * If 0 — log everything.
         */
        private readonly int $threshold = 0,

        /**
         * Logging driver: log | telescope | both.
         */
        private readonly string $driver = 'log',
    ) {
    }

    /**
     * Start a measurement (manual state handling).
     *
     * Use this for simple inline measurements
     * where the start and stop calls are close together.
     *
     * @return array{t0: float, m0: int, p0: int, type: string} Context to be passed to stop().
     */
    public function start(string $type = '', ): array
    {
        return [
            't0' => microtime(true),
            'm0' => memory_get_usage(true),
            'p0' => memory_get_peak_usage(true),
            'type' => $type,
        ];
    }

    /**
     * Finish a measurement started with start().
     *
     * @param array{t0: float, m0: int, p0: int, type: string} $ctx Context returned by start().
     * @param array $extra Additional metadata for logging.
     *
     * @return array Measurement results.
     */
    public function stop(array $ctx, array $extra = []): array
    {
        $t1 = microtime(true);
        $m1 = memory_get_usage(true);
        $p1 = memory_get_peak_usage(true);

        $data = array_merge([
            'duration_ms' => (int)round(($t1 - $ctx['t0']) * 1000),
            'memory_start' => $ctx['m0'],
            'memory_end' => $m1,
            'memory_peak' => max($ctx['p0'], $p1),
            'memory_diff' => $m1 - $ctx['m0'],
            'type' => $ctx['type'],
        ], $extra);

        $this->dispatch($data);
        return $data;
    }

    /**
     * Dispatch measurement results to the configured logging driver(s).
     *
     * @param array $data Measurement data.
     */
    protected function dispatch(array $data): void
    {
        if ($this->threshold > 0 && ($data['memory_peak'] ?? 0) < $this->threshold) {
            return;
        }

        if ($this->driver === 'log' || $this->driver === 'both') {
            $this->toLog($data);
        }
        if ($this->driver === 'telescope' || $this->driver === 'both') {
            $this->toTelescope($data);
        }
    }

    /**
     * Log measurement data to a dedicated log file.
     *
     * @param array $data Measurement data.
     */
    protected function toLog(array $data): void
    {
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/memory_profiler.log'),
            'level' => 'info',
        ]);

        $logger->info('Memory profile', $data);
    }

    /**
     * Record measurement data in Laravel Telescope.
     *
     * @param array $data Measurement data.
     */
    protected function toTelescope(array $data): void
    {
        if (class_exists(\Laravel\Telescope\Telescope::class)
            && class_exists(\Laravel\Telescope\IncomingEntry::class)) {

            \Laravel\Telescope\Telescope::recordLog(
                \Laravel\Telescope\IncomingEntry::make([
                    'message' => isset($data['type']) ? 'Memory profile '. $data['type'] : 'Memory profile',
                    'context' => $data,
                    'level'   => 'info',
                ])->tags(['memory'])
            );
        }
    }
}
