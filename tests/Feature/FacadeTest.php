<?php

namespace Phpner\MemoryProfiler\Tests\Feature;

use Phpner\MemoryProfiler\Facades\MemoryProfiler as MP;
use Phpner\MemoryProfiler\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_begin_end(): void
    {
        $span = MP::begin('facade.segment');
        usleep(5_000);
        MP::end($span, ['k' => 1]);

        $log = file_exists($this->memoryLogPath()) ? file_get_contents($this->memoryLogPath()) : '';
        $this->assertStringContainsString('"label":"facade.segment"', $log);
        $this->assertStringContainsString('"type":"code"', $log);
    }
}
