<?php

declare(strict_types=1);

namespace GusBundle\Client\SearchHandler;

use GusBundle\Exception\InvalidKrsException;
use GusApi\GusApi;
use Psr\Log\LoggerInterface;
use GusApi\SearchReport;

final class KrsSearchHandler extends AbstractSearchHandler
{
    protected function validate(string $identifier): bool
    {
        return strlen($identifier) === 10 && ctype_digit($identifier);
    }

    protected function getIdentifierType(): string
    {
        return 'KRS';
    }

    /**
     * @return SearchReport[]
     */
    public function __invoke(string $identifier): array
    {
        return $this->gusApi->getByKrs($identifier);
    }

    protected function createValidationException(string $identifier): \Exception
    {
        return new InvalidKrsException("Invalid KRS number: {$identifier}");
    }
}
