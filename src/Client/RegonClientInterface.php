<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Client;

use GusApi\SearchReport;

interface RegonClientInterface
{
    public function getByRegon(string $regon): SearchReport;

    public function getByNip(string $nip): SearchReport;

    public function getByKrs(string $krs): SearchReport;

    public function login(): void;

    public function logout(): void;
}
