<?php

namespace Phpner\MemoryProfiler\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Phpner\MemoryProfiler\Middleware\HttpProfiler;
use Phpner\MemoryProfiler\Tests\TestCase;

class HttpProfilerMiddlewareTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::middleware(HttpProfiler::class)->get('/_probe', fn () => response('ok'));
    }

    public function test_headers_and_log_written(): void
    {
        $resp = $this->get('/_probe');
        $resp->assertOk();
        $resp->assertHeader('X-Memory-Peak');
        $resp->assertHeader('X-Duration-ms');

        $this->assertFileExists($this->memoryLogPath());
        $log = file_get_contents($this->memoryLogPath());
        $this->assertStringContainsString('"type":"http"', $log);
        $this->assertStringContainsString('"_probe"', $log);
    }
}
