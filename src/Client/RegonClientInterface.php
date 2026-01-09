<?php

declare(strict_types=1);

namespace GusBundle\Client;

use GusApi\SearchReport;

interface RegonClientInterface
{
    public function getByRegon(string $regon): SearchReport;

    public function getByNip(string $nip): SearchReport;

    public function getByKrs(string $krs): SearchReport;

    /**
     * @return array<int, array<string, string>>
     */
    public function getFullReport(SearchReport $searchReport, string $reportName): array;

    public function login(): void;

    public function logout(): void;
}
