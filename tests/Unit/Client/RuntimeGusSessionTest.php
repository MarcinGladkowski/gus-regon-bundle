<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit\Client;

use GusBundle\Client\RuntimeGusSession;
use GusBundle\Exception\ApiAuthenticationException;
use GusBundle\Exception\ApiConnectionException;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\GusApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RuntimeGusSessionTest extends TestCase
{
    private GusApi&MockObject $gusApiMock;
    private LoggerInterface&MockObject $loggerMock;
    private RuntimeGusSession $gusSession;

    protected function setUp(): void
    {
        $this->gusApiMock = $this->createMock(GusApi::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->gusSession = new RuntimeGusSession($this->gusApiMock, $this->loggerMock);
    }

    public function testLoginSuccess(): void
    {
        $this->gusApiMock->expects($this->once())
            ->method('login');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Successfully logged in to GUS API');

        $this->gusSession->login();
        $this->assertTrue($this->gusSession->isLoggedIn());
    }

    public function testLoginIsIdempotent(): void
    {
        $this->gusApiMock->expects($this->once())
            ->method('login');

        $this->gusSession->login();
        $this->gusSession->login(); // Should be ignored
    }

    public function testLoginAuthenticationException(): void
    {
        $exception = new InvalidUserKeyException('Invalid key');
        $this->gusApiMock->expects($this->once())
            ->method('login')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Failed to login - invalid API key', ['exception' => $exception]);

        $this->expectException(ApiAuthenticationException::class);
        $this->gusSession->login();
    }

    public function testLoginConnectionException(): void
    {
        $exception = new \SoapFault('Server', 'SOAP-ERROR: Parsing WSDL');
        $this->gusApiMock->expects($this->once())
            ->method('login')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Failed to login - SOAP error', ['exception' => $exception]);

        $this->expectException(ApiConnectionException::class);
        $this->gusSession->login();
    }

    public function testLogoutSuccess(): void
    {
        // Setup state to logged in
        $this->gusApiMock->method('login');
        $this->gusSession->login();

        $this->gusApiMock->expects($this->once())
            ->method('logout');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Successfully logged out from GUS API');

        $this->gusSession->logout();
        $this->assertFalse($this->gusSession->isLoggedIn());
    }

    public function testLogoutWhenNotLoggedIn(): void
    {
        $this->gusApiMock->expects($this->never())
            ->method('logout');

        $this->gusSession->logout();
    }

    public function testLogoutExceptionHandling(): void
    {
        // Setup state to logged in
        $this->gusApiMock->method('login');
        $this->gusSession->login();

        $exception = new \RuntimeException('Something went wrong');
        $this->gusApiMock->expects($this->once())
            ->method('logout')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Failed to logout gracefully', ['exception' => $exception]);

        $this->gusSession->logout();
        // State should still be reset to false? Or remains true?
        // Current implementation does NOT reset isLoggedIn to false if logout throws.
        // Let's check implementation:
        // try { logout; isLoggedIn=false; } catch { warning }
        // So it stays true if exception occurs during logout.
        // This seems correct as logout failed.
        $this->assertTrue($this->gusSession->isLoggedIn());
    }

    public function testEnsureLoggedInCallsLoginWhenNotLoggedIn(): void
    {
        $this->gusApiMock->expects($this->once())
            ->method('login');

        $this->gusSession->ensureLoggedIn();
        $this->assertTrue($this->gusSession->isLoggedIn());
    }

    public function testEnsureLoggedInDoesNothingWhenAlreadyLoggedIn(): void
    {
        $this->gusApiMock->expects($this->once())
            ->method('login');

        $this->gusSession->login();
        $this->gusSession->ensureLoggedIn();
    }
}
