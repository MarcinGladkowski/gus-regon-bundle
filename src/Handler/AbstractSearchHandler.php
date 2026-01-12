<?php

declare(strict_types=1);

namespace GusBundle\Handler;

use GusBundle\Collection\SearchReportCollection;
use GusBundle\Exception\ApiAuthenticationException;
use GusBundle\Exception\ApiConnectionException;
use GusBundle\Exception\CompanyNotFoundException;
use GusBundle\Exception\GusApiException;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\SearchReport;
use GusApi\Type\Response\SearchResponseCompanyData;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract class AbstractSearchHandler
{
    public function __construct(
        protected readonly GusApi $gusApi,
        protected readonly LoggerInterface $logger
    ) {
    }

    abstract protected function validate(string $identifier): bool;

    abstract protected function getIdentifierType(): string;

    abstract protected function createValidationException(string $identifier): \Exception;

    public function search(string $identifier): SearchReportCollection
    {
        if (!$this->validate($identifier)) {
            throw $this->createValidationException($identifier);
        }

        try {
            $result = $this($identifier);

            return new SearchReportCollection($result);

        } catch (NotFoundException $e) {
            $this->logger->info("{$this->getIdentifierType()} not found", [$this->getIdentifierType() => $identifier]);
            throw new CompanyNotFoundException(
                "Business with {$this->getIdentifierType()} {$identifier} not found",
                '',
                0,
                $e
            );
        } catch (InvalidUserKeyException $e) {
            $this->logger->error('Invalid API key', ['exception' => $e]);
            throw new ApiAuthenticationException('Invalid API key', '', 0, $e);
        } catch (\SoapFault $e) {
            $this->logger->error('SOAP connection error', ['exception' => $e]);
            throw new ApiConnectionException('Failed to connect to GUS API', '', 0, $e);
        } catch (GusApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during {$this->getIdentifierType()} lookup", ['exception' => $e]);
            throw new ApiConnectionException('Unexpected error: ' . $e->getMessage(), '', 0, $e);
        }
    }

}
