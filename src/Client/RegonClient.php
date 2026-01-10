<?php

declare(strict_types=1);

namespace GusBundle\Client;

use GusBundle\Handler\FullReportHandler;
use GusBundle\Handler\KrsSearchHandler;
use GusBundle\Handler\NipSearchHandler;
use GusBundle\Handler\RegonSearchHandler;
use GusBundle\Exception\ApiAuthenticationException;
use GusBundle\Exception\ApiConnectionException;
use GusBundle\Exception\CompanyNotFoundException;
use GusBundle\Exception\InvalidKrsException;
use GusBundle\Exception\InvalidNipException;
use GusBundle\Exception\InvalidRegonException;
use GusBundle\Validator\NipValidator;
use GusBundle\Validator\RegonValidator;
use GusApi\GusApi;
use GusApi\SearchReport;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class RegonClient implements RegonClientInterface
{
    private const ENVIRONMENT_TEST = 'test';
    private const ENVIRONMENT_PROD = 'production';

    private GusApi $gusApi;
    private GusSessionInterface $session;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $environment,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ?CacheItemPoolInterface $cache = null
    ) {
        $this->initializeGusApi();

        $session = new RuntimeGusSession($this->gusApi, $this->logger);

        if ($this->cache) {
            $session = new CachedGusSession(
                $session,
                $this->gusApi,
                $this->cache,
                'gus_session_' . md5($this->apiKey),
                $this->logger
            );
        }

        $this->session = $session;
    }

    /**
     * @param string $regon
     * @throws CompanyNotFoundException
     * @throws InvalidRegonException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByRegon(string $regon): SearchReport
    {
        $this->session->ensureLoggedIn();
        return (new RegonSearchHandler($this->gusApi, $this->logger, new RegonValidator()))->searchSingle($regon);
    }

    /**
     * @param string $nip
     * @throws InvalidNipException
     * @throws CompanyNotFoundException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByNip(string $nip): SearchReport
    {
        $this->session->ensureLoggedIn();
        return (new NipSearchHandler($this->gusApi, $this->logger, new NipValidator()))->searchSingle($nip);
    }

    /**
     * @param string $krs
     * @throws InvalidKrsException
     * @throws CompanyNotFoundException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByKrs(string $krs): SearchReport
    {
        $this->session->ensureLoggedIn();

        return (new KrsSearchHandler($this->gusApi, $this->logger))->searchSingle($krs);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getFullReport(SearchReport $searchReport, string $reportName): array
    {
        $this->session->ensureLoggedIn();

        return (new FullReportHandler($this->gusApi, $this->logger))($searchReport, $reportName);
    }

    public function login(): void
    {
        $this->session->login();
    }

    public function logout(): void
    {
        $this->session->logout();
    }

    private function initializeGusApi(): void
    {
        $env = match ($this->environment) {
            self::ENVIRONMENT_TEST => 'dev',
            self::ENVIRONMENT_PROD => 'prod',
            default => throw new \InvalidArgumentException('Invalid environment specified'),
        };

        $this->gusApi = new GusApi(
            $this->apiKey,
            $env
        );
    }
}
