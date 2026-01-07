<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client;

use MarcinGladkowski\GusBundle\Client\SearchHandler\KrsSearchHandler;
use MarcinGladkowski\GusBundle\Client\SearchHandler\NipSearchHandler;
use MarcinGladkowski\GusBundle\Client\SearchHandler\RegonSearchHandler;
use MarcinGladkowski\GusBundle\Exception\ApiAuthenticationException;
use MarcinGladkowski\GusBundle\Exception\ApiConnectionException;
use MarcinGladkowski\GusBundle\Validator\NipValidator;
use MarcinGladkowski\GusBundle\Validator\RegonValidator;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\GusApi;
use GusApi\SearchReport;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class RegonClient implements RegonClientInterface
{
    private const ENVIRONMENT_TEST = 'test';
    private const ENVIRONMENT_PROD = 'production';

    private const ENDPOINTS = [
        self::ENVIRONMENT_TEST => 'https://wyszukiwarkaregontest.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc',
        self::ENVIRONMENT_PROD => 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc',
    ];

    private GusApi $gusApi;
    private bool $isLoggedIn = false;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $environment = self::ENVIRONMENT_TEST,
        private readonly RegonValidator $regonValidator = new RegonValidator(),
        private readonly NipValidator $nipValidator = new NipValidator(),
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        $this->initializeGusApi();
    }

    /**
     * @param string $regon
     * @throws \App\GusBundle\Exception\InvalidRegonException
     * @throws \App\GusBundle\Exception\CompanyNotFoundException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByRegon(string $regon): SearchReport
    {
        $this->ensureLoggedIn();
        return (new RegonSearchHandler($this->gusApi, $this->logger, $this->regonValidator))->searchSingle($regon);
    }

    /**
     * @param string $nip
     * @throws \App\GusBundle\Exception\InvalidNipException
     * @throws \App\GusBundle\Exception\CompanyNotFoundException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByNip(string $nip): SearchReport
    {
        $this->ensureLoggedIn();
        return (new NipSearchHandler($this->gusApi, $this->logger, $this->nipValidator))->searchSingle($nip);
    }

    /**
     * @param string $krs
     * @throws \App\GusBundle\Exception\InvalidKrsException
     * @throws \App\GusBundle\Exception\CompanyNotFoundException
     * @throws ApiAuthenticationException
     * @throws ApiConnectionException
     * @return SearchReport
     */
    public function getByKrs(string $krs): SearchReport
    {
        $this->ensureLoggedIn();

        return (new KrsSearchHandler($this->gusApi, $this->logger))->searchSingle($krs);
    }

    public function login(): void
    {
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

    private function initializeGusApi(): void
    {
        $this->gusApi = new GusApi(
            $this->apiKey,
            $this->environment
        );
    }

    private function ensureLoggedIn(): void
    {
        if (!$this->isLoggedIn) {
            $this->login();
        }
    }
}
