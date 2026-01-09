<?php

declare(strict_types=1);

namespace GusBundle\Handler;

use GusApi\GusApi;
use GusApi\SearchReport;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class FullReportHandler extends AbstractSearchHandler
{
    public function validate(string $identifier): bool
    {
        // @todo temporary stub, implement proper validation if needed
        return true;
    }

    protected function getIdentifierType(): string
    {
        return 'FullReport';
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function __invoke(SearchReport $searchReport, string $reportName): array
    {
        return $this->gusApi->getFullReport($searchReport, $reportName);
    }

    protected function createValidationException(string $identifier): \Exception
    {
        // @todo to specified exception
        return new RuntimeException('Invalid full report identifier: ' . $identifier);
    }
}
