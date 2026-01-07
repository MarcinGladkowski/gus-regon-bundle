<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client\SearchHandler;

use MarcinGladkowski\GusBundle\Exception\InvalidNipException;
use MarcinGladkowski\GusBundle\Validator\NipValidator;
use GusApi\GusApi;
use Psr\Log\LoggerInterface;

final class NipSearchHandler extends AbstractSearchHandler
{
    public function __construct(
        GusApi $gusApi,
        LoggerInterface $logger,
        private readonly NipValidator $nipValidator
    ) {
        parent::__construct($gusApi, $logger);
    }

    protected function validate(string $identifier): bool
    {
        return $this->nipValidator->validate($identifier);
    }

    protected function getIdentifierType(): string
    {
        return 'NIP';
    }

    protected function performSearch(string $identifier): array
    {
        return $this->gusApi->getByNip($identifier);
    }

    protected function createValidationException(string $identifier): \Exception
    {
        return new InvalidNipException("Invalid NIP number: {$identifier}");
    }
}
