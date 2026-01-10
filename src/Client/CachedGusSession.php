<?php

declare(strict_types=1);

namespace GusBundle\Client;

use GusApi\GusApi;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final class CachedGusSession implements GusSessionInterface
{
    private const SESSION_TTL = 3300; // 55 minutes (GUS session lasts 60m)

    private bool $isLoggedIn = false;

    public function __construct(
        private readonly GusSessionInterface $innerSession,
        private readonly GusApi $gusApi, // Needed to set/get session ID directly
        private readonly CacheItemPoolInterface $cache,
        private readonly string $cacheKey,
        private readonly LoggerInterface $logger
    ) {
    }

    public function login(): void
    {
        if ($this->isLoggedIn) {
            return;
        }

        if ($this->restoreSessionFromCache()) {
            return;
        }

        $this->innerSession->login();
        $this->isLoggedIn = true;

        $this->saveSessionToCache();
    }

    public function logout(): void
    {
        // First remove from cache to ensure no one picks up a dead session
        try {
            $this->cache->deleteItem($this->cacheKey);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to remove session from cache', ['exception' => $e]);
        }

        // Then perform actual logout
        $this->innerSession->logout();
        $this->isLoggedIn = false;
    }

    public function ensureLoggedIn(): void
    {
        if (!$this->isLoggedIn) {
            $this->login();
        }
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    private function restoreSessionFromCache(): bool
    {
        try {
            $item = $this->cache->getItem($this->cacheKey);
            if ($item->isHit()) {
                $sessionId = $item->get();
                if (is_string($sessionId) && !empty($sessionId)) {
                    $this->gusApi->setSessionId($sessionId);
                    $this->isLoggedIn = true;
                    $this->logger->debug('Session restored from cache', ['key' => $this->cacheKey]);

                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to restore session from cache', ['exception' => $e]);
        }

        return false;
    }

    private function saveSessionToCache(): void
    {
        try {
            $sessionId = $this->gusApi->getSessionId();
            $item = $this->cache->getItem($this->cacheKey);
            $item->set($sessionId);
            $item->expiresAfter(self::SESSION_TTL);
            $this->cache->save($item);
            $this->logger->debug('Session saved to cache', ['key' => $this->cacheKey]);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to save session to cache', ['exception' => $e]);
        }
    }
}
