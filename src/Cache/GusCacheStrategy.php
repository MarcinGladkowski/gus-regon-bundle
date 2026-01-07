<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Cache;

use MarcinGladkowski\GusBundle\DTO\BusinessDataDTO;
use GusApi\SearchReport;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GusCacheStrategy
{
    private const CACHE_KEY_PREFIX = 'gus_';

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly int $ttl = 86400,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function get(string $key): ?SearchReport
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $item = $this->cache->getItem($cacheKey);

            if ($item->isHit()) {
                $data = $item->get();

                if ($data instanceof SearchReport) {
                    $this->logger->debug('Cache hit', ['key' => $key]);
                    return $data;
                }
            }

            $this->logger->debug('Cache miss', ['key' => $key]);
            return null;
        } catch (\Exception $e) {
            $this->logger->warning('Cache get failed', ['key' => $key, 'exception' => $e]);
            return null;
        }
    }

    public function set(string $key, mixed $data): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $item = $this->cache->getItem($cacheKey);

            $item->set($data);
            $item->expiresAfter($this->ttl);

            $result = $this->cache->save($item);

            if ($result) {
                $this->logger->debug('Cache set successful', ['key' => $key, 'ttl' => $this->ttl]);
            } else {
                $this->logger->warning('Cache set failed', ['key' => $key]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache set failed', ['key' => $key, 'exception' => $e]);
            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            $cacheKey = $this->buildCacheKey($key);
            $result = $this->cache->deleteItem($cacheKey);

            if ($result) {
                $this->logger->debug('Cache delete successful', ['key' => $key]);
            } else {
                $this->logger->warning('Cache delete failed', ['key' => $key]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache delete failed', ['key' => $key, 'exception' => $e]);
            return false;
        }
    }

    public function clear(): bool
    {
        try {
            $result = $this->cache->clear();

            if ($result) {
                $this->logger->info('Cache cleared successfully');
            } else {
                $this->logger->warning('Cache clear failed');
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache clear failed', ['exception' => $e]);
            return false;
        }
    }

    private function buildCacheKey(string $key): string
    {
        return self::CACHE_KEY_PREFIX . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    }
}
