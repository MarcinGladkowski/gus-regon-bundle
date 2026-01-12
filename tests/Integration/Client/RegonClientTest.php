<?php

declare(strict_types=1);

namespace GusBundle\Tests\Integration\Client;

use GusBundle\Client\RegonClient;
use GusBundle\Exception\ApiAuthenticationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class RegonClientTest extends TestCase
{
    private const TEST_API_KEY = 'abcde12345abcde12345'; // Standard GUS test key

    private RegonClient $client;

    public static function setUpBeforeClass(): void
    {
        try {
            $client = new RegonClient(self::TEST_API_KEY, 'test', new ArrayAdapter());
            $client->login();
        } catch (ApiAuthenticationException $e) {
            self::markTestSkipped('GUS API Authentication failed. Check your API key or GUS service availability.');
        } catch (\SoapFault $e) {
            self::markTestSkipped('GUS API Connection failed (SOAP Fault). Service might be unavailable.');
        } catch (\Throwable $e) {
            self::markTestSkipped(sprintf('GUS API check failed: %s', $e->getMessage()));
        }
    }

    protected function setUp(): void
    {
        $this->client = new RegonClient(self::TEST_API_KEY, 'test', new ArrayAdapter());
    }

    public function testLoginFailureWithInvalidKey(): void
    {
        $client = new RegonClient('invalid-key', 'test', new ArrayAdapter());

        $this->expectException(ApiAuthenticationException::class);
        $client->login();
    }

    public function testGetByRegonWithValidRegon(): void
    {
        // Use a known valid/test REGON.
        // 000331501 is GUS REGON
        $regon = '000331501';

        $report = $this->client->getByRegon($regon);
        $this->assertNotEmpty($report->first()->getName());
    }

    public function testGetByNipWithValidNip(): void
    {
        // 5261040828 is GUS NIP
        $nip = '5261040828';

        $report = $this->client->getByNip($nip);
        $this->assertNotEmpty($report->first()->getName());
    }

    public function testGetByKrsWithValidKrs(): void
    {
        // 0000123456 - standard 10 digit KRS
        $krs = '0000123456';

        $report = $this->client->getByKrs($krs);
        // SearchReport might be empty if not found, but we expect it to be return valid object.
        // In Sandbox "12..." usually returns something or valid structure.
        $this->assertNotNull($report);
        $this->assertFalse($report->isEmpty());
    }

    public function testGetFullReportWithValidRegon(): void
    {
        // 000331501 - GUS
        $regon = '000331501';
        // BIR11OsPrawna is standard, but the underlying library validation expects BIR12OsPrawna
        // as per the exception message seen in test failures.
        $reportName = 'BIR12OsPrawna';

        $searchReportCollection = $this->client->getByRegon($regon);

        $fullReport = $this->client->getFullReport($searchReportCollection->first(), $reportName);

        $this->assertIsArray($fullReport);
        $this->assertNotEmpty($fullReport);
    }
}
