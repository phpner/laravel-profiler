<?php

namespace Phpner\MemoryProfiler\Tests\Unit;

use Phpner\MemoryProfiler\MemoryProfiler;
use Phpner\MemoryProfiler\Tests\TestCase;

class MemoryProfilerTest extends TestCase
{
    public function test_begin_end_writes_to_default_file(): void
    {
        /** @var MemoryProfiler $prof */
        $prof = $this->app->make(MemoryProfiler::class);

        $id = $prof->begin('unit.segment', ['foo' => 'bar']);
        usleep(10_000);
        $prof->end($id, ['baz' => 'qux']);

        $this->assertFileExists($this->memoryLogPath());
        $log = file_get_contents($this->memoryLogPath());
        $this->assertStringContainsString('Memory profile', $log);
        $this->assertStringContainsString('"label":"unit.segment"', $log);
        $this->assertStringContainsString('"type":"code"', $log);
    }

}
