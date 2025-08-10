<?php

namespace Phpner\MemoryProfiler\Tests\Feature;

use Illuminate\Console\Command;
use Phpner\MemoryProfiler\Console\Concerns\ProfilesMemory;
use Phpner\MemoryProfiler\Tests\TestCase;

class ArtisanTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $cmd = new class extends Command {
            use ProfilesMemory;
            protected $signature = 'ov:test-prof';
            public function handle(): int
            {
                return $this->withMemoryProfiling(function () {
                    usleep(3_000);
                    return self::SUCCESS;
                });
            }
        };
        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($cmd);
    }

    public function test_artisan_profiler_logs(): void
    {
        $this->artisan('ov:test-prof')->assertExitCode(0);

        $log = file_exists($this->memoryLogPath()) ? file_get_contents($this->memoryLogPath()) : '';
        $this->assertStringContainsString('"type":"artisan"', $log);
        $this->assertStringContainsString('"command":"ov:test-prof"', $log);
    }
}
