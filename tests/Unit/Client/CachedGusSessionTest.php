<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit\Client;

use GusBundle\Client\CachedGusSession;
use GusBundle\Client\GusSessionInterface;
use GusApi\GusApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class CachedGusSessionTest extends TestCase
{
    private GusSessionInterface&MockObject $innerSessionMock;
    private GusApi&MockObject $gusApiMock;
    private CacheItemPoolInterface&MockObject $cacheMock;
    private LoggerInterface&MockObject $loggerMock;
    private CachedGusSession $cachedSession;
    private string $cacheKey = 'test_key';

    protected function setUp(): void
    {
        $this->innerSessionMock = $this->createMock(GusSessionInterface::class);
        $this->gusApiMock = $this->createMock(GusApi::class);
        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->cachedSession = new CachedGusSession(
            $this->innerSessionMock,
            $this->gusApiMock,
            $this->cacheMock,
            $this->cacheKey,
            $this->loggerMock
        );
    }

    public function testLoginUsesCacheHit(): void
    {
        $sessionId = 'cached_session_id';
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($sessionId);

        $this->cacheMock->expects($this->once())
            ->method('getItem')
            ->with($this->cacheKey)
            ->willReturn($cacheItem);

        $this->gusApiMock->expects($this->once())
            ->method('setSessionId')
            ->with($sessionId);

        $this->innerSessionMock->expects($this->never())->method('login');

        $this->cachedSession->login();
        $this->assertTrue($this->cachedSession->isLoggedIn());
    }

    public function testLoginCacheMissDelegatesToInnerSessionAndSaves(): void
    {
        $sessionId = 'new_session_id';
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $cacheItem->expects($this->once())->method('set')->with($sessionId);
        $cacheItem->expects($this->once())->method('expiresAfter')->with(3300);

        $this->cacheMock->expects($this->exactly(2))
            ->method('getItem')
            ->with($this->cacheKey)
            ->willReturn($cacheItem);

        $this->innerSessionMock->expects($this->once())->method('login');
        
        $this->gusApiMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $this->cachedSession->login();
        $this->assertTrue($this->cachedSession->isLoggedIn());
    }

    public function testLogoutClearsCacheAndDelegates(): void
    {
        $this->cacheMock->expects($this->once())
            ->method('deleteItem')
            ->with($this->cacheKey);

        $this->innerSessionMock->expects($this->once())->method('logout');

        $this->cachedSession->logout();
        $this->assertFalse($this->cachedSession->isLoggedIn());
    }
}
