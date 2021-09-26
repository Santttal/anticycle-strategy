<?php

namespace App\Service;

use Redis;

class SyncDecisionMaker
{
    private const SYNC_TTL = 24 * 60 * 60;
    private const SYNC_KEY = 'is_synced';

    public function __construct(
        private Redis $redis,
    ) {
    }

    public function shouldSync(): bool
    {
        var_export($this->redis->ttl(self::SYNC_KEY));

        return !$this->redis->exists(self::SYNC_KEY);
    }

    public function markAsSynced(): void
    {
        $this->redis->setex(self::SYNC_KEY, self::SYNC_TTL, true);
    }
}
