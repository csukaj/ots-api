<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

/**
 * Flush Redis cache
 */
class FlushRedisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:flushredis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flushes Redis cache';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Redis::flushall();
        $this->info("Redis flushed.");
    }
}
