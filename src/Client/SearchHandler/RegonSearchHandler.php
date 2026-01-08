<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client\SearchHandler;

use MarcinGladkowski\GusBundle\Exception\InvalidRegonException;
use MarcinGladkowski\GusBundle\Validator\RegonValidator;
use GusApi\GusApi;
use Psr\Log\LoggerInterface;
use GusApi\SearchReport;

final class RegonSearchHandler extends AbstractSearchHandler
{
    public function __construct(
        GusApi $gusApi,
        LoggerInterface $logger,
        private readonly RegonValidator $regonValidator
    ) {
        parent::__construct($gusApi, $logger);
    }

    protected function validate(string $identifier): bool
    {
        return $this->regonValidator->validate($identifier);
    }

    protected function getIdentifierType(): string
    {
        return 'REGON';
    }

    /**
     * @return SearchReport[]
     */
    public function __invoke(string $identifier): array
    {
        return $this->gusApi->getByRegon($identifier);
    }

    protected function createValidationException(string $identifier): \Exception
    {
        return new InvalidRegonException("Invalid REGON number: {$identifier}");
    }
}
