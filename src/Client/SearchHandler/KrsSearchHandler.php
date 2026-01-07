<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client\SearchHandler;

use MarcinGladkowski\GusBundle\Exception\InvalidKrsException;
use GusApi\GusApi;
use Psr\Log\LoggerInterface;

final class KrsSearchHandler extends AbstractSearchHandler
{
    public function __construct(
        GusApi $gusApi,
        LoggerInterface $logger
    ) {
        parent::__construct($gusApi, $logger);
    }

    protected function validate(string $identifier): bool
    {
        return strlen($identifier) === 10 && ctype_digit($identifier);
    }

    protected function getIdentifierType(): string
    {
        return 'KRS';
    }

    protected function performSearch(string $identifier): array
    {
        return $this->gusApi->getByKrs($identifier);
    }

    protected function createValidationException(string $identifier): \Exception
    {
        return new InvalidKrsException("Invalid KRS number: {$identifier}");
    }
}
