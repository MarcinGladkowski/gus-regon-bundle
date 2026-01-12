<?php

declare(strict_types=1);

namespace GusBundle\Collection;

use GusApi\SearchReport;
use GusBundle\Exception\CompanyNotFoundException;

class SearchReportCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var SearchReport[]
     */
    private array $reports;

    /**
     * @param SearchReport[] $reports
     */
    public function __construct(array $reports = [])
    {
        $this->reports = array_values($reports);
    }

    public function count(): int
    {
        return count($this->reports);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->reports);
    }

    /**
     * @return SearchReport[]
     */
    public function all(): array
    {
        return $this->reports;
    }

    public function first(): SearchReport
    {
        if (empty($this->reports)) {
            throw new CompanyNotFoundException('No company data found.');
        }

        return $this->reports[0];
    }

    public function isEmpty(): bool
    {
        return empty($this->reports);
    }
}
