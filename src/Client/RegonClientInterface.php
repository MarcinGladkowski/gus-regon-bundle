<?php

declare(strict_types=1);

namespace GusBundle\Client;

use GusBundle\Collection\SearchReportCollection;
use GusApi\SearchReport;

interface RegonClientInterface
{
    public function getByRegon(string $regon): SearchReportCollection;

    public function getByNip(string $nip): SearchReportCollection;

    public function getByKrs(string $krs): SearchReportCollection;

    /**
     * @return array<int, array<string, string>>
     */
    public function getFullReport(SearchReport $searchReport, string $reportName): array;

    public function login(): void;

    public function logout(): void;
}
