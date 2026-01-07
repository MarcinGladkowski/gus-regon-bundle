<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client;

use MarcinGladkowski\GusBundle\Exception\ApiAuthenticationException;
use MarcinGladkowski\GusBundle\Exception\ApiConnectionException;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\GusApi;
use Psr\Log\LoggerInterface;

class GusSession
{
    private bool $isLoggedIn = false;

    public function __construct(
        private readonly GusApi $gusApi,
        private readonly LoggerInterface $logger
    ) {
    }

    public function login(): void
    {
        if ($this->isLoggedIn) {
            return;
        }

        try {
            $this->gusApi->login();
            $this->isLoggedIn = true;
            $this->logger->info('Successfully logged in to GUS API');
        } catch (InvalidUserKeyException $e) {
            $this->logger->error('Failed to login - invalid API key', ['exception' => $e]);
            throw new ApiAuthenticationException('Invalid API key', '', 0, $e);
        } catch (\SoapFault $e) {
            $this->logger->error('Failed to login - SOAP error', ['exception' => $e]);
            throw new ApiConnectionException('Failed to connect to GUS API', '', 0, $e);
        }
    }

    public function logout(): void
    {
        if (!$this->isLoggedIn) {
            return;
        }

        try {
            $this->gusApi->logout();
            $this->isLoggedIn = false;
            $this->logger->info('Successfully logged out from GUS API');
        } catch (\Exception $e) {
            $this->logger->warning('Failed to logout gracefully', ['exception' => $e]);
        }
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
}
