<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client\SearchHandler;

use MarcinGladkowski\GusBundle\Exception\ApiAuthenticationException;
use MarcinGladkowski\GusBundle\Exception\ApiConnectionException;
use MarcinGladkowski\GusBundle\Exception\CompanyNotFoundException;
use MarcinGladkowski\GusBundle\Exception\GusApiException;
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
    
    abstract protected function performSearch(string $identifier): array;

    abstract protected function createValidationException(string $identifier): \Exception;

    public function searchSingle(string $identifier): SearchReport
    {
        if (!$this->validate($identifier)) {
            throw $this->createValidationException($identifier);
        }

        try {
            /**  @var $result SearchReport[] */
            $result = $this->performSearch($identifier);

            if (!empty($result) && count($result) === 1) {
                return $result[0];
            }

            if (count($result) > 1) {
                throw new RuntimeException('Multiple results found when single expected');
            }
            
            return new SearchReport(new SearchResponseCompanyData());

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
