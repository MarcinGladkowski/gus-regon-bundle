<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit\Client\SearchHandler;

use GusBundle\Client\SearchHandler\KrsSearchHandler;
use GusBundle\Exception\ApiAuthenticationException;
use GusBundle\Exception\ApiConnectionException;
use GusBundle\Exception\CompanyNotFoundException;
use GusBundle\Exception\InvalidKrsException;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\SearchReport;
use GusApi\Type\Response\SearchResponseCompanyData;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class KrsSearchHandlerTest extends TestCase
{
    private GusApi $gusApi;
    private LoggerInterface $logger;
    private KrsSearchHandler $handler;

    protected function setUp(): void
    {
        $this->gusApi = $this->createMock(GusApi::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new KrsSearchHandler(
            $this->gusApi,
            $this->logger
        );
    }

    public function testSearchSingleWithValidKrsReturnsSearchReport(): void
    {
        $krs = '0000123456';
        $searchReport = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willReturn([$searchReport]);

        $result = $this->handler->searchSingle($krs);

        $this->assertInstanceOf(SearchReport::class, $result);
        $this->assertSame($searchReport, $result);
    }

    public function testSearchSingleWithNonNumericKrsThrowsException(): void
    {
        $invalidKrs = '000012345A';

        $this->expectException(InvalidKrsException::class);
        $this->expectExceptionMessage("Invalid KRS number: {$invalidKrs}");

        $this->handler->searchSingle($invalidKrs);
    }

    public function testSearchSingleWithInvalidLengthThrowsException(): void
    {
        $invalidKrs = '123456789';

        $this->expectException(InvalidKrsException::class);
        $this->expectExceptionMessage("Invalid KRS number: {$invalidKrs}");

        $this->handler->searchSingle($invalidKrs);
    }

    public function testSearchSingleWithTooLongKrsThrowsException(): void
    {
        $invalidKrs = '12345678901';

        $this->expectException(InvalidKrsException::class);
        $this->expectExceptionMessage("Invalid KRS number: {$invalidKrs}");

        $this->handler->searchSingle($invalidKrs);
    }

    public function testSearchSingleWhenNotFoundThrowsCompanyNotFoundException(): void
    {
        $krs = '0000123456';

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willThrowException(new NotFoundException('Not found'));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('KRS not found', ['KRS' => $krs]);

        $this->expectException(CompanyNotFoundException::class);
        $this->expectExceptionMessage("Business with KRS {$krs} not found");

        $this->handler->searchSingle($krs);
    }

    public function testSearchSingleWithInvalidApiKeyThrowsApiAuthenticationException(): void
    {
        $krs = '0000123456';

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willThrowException(new InvalidUserKeyException('Invalid key'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Invalid API key', $this->anything());

        $this->expectException(ApiAuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->handler->searchSingle($krs);
    }

    public function testSearchSingleWithSoapFaultThrowsApiConnectionException(): void
    {
        $krs = '0000123456';

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willThrowException(new \SoapFault('Server', 'Connection error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('SOAP connection error', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Failed to connect to GUS API');

        $this->handler->searchSingle($krs);
    }

    public function testSearchSingleWithMultipleResultsThrowsException(): void
    {
        $krs = '0000123456';
        $searchReport1 = new SearchReport(new SearchResponseCompanyData());
        $searchReport2 = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willReturn([$searchReport1, $searchReport2]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unexpected error: Multiple results found when single expected");

        $this->handler->searchSingle($krs);
    }

    public function testSearchSingleWithEmptyResultReturnsEmptySearchReport(): void
    {
        $krs = '0000123456';

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willReturn([]);

        $result = $this->handler->searchSingle($krs);

        $this->assertInstanceOf(SearchReport::class, $result);
    }

    public function testSearchSingleWithUnexpectedExceptionThrowsApiConnectionException(): void
    {
        $krs = '0000123456';
        $unexpectedException = new \RuntimeException('Unexpected error');

        $this->gusApi->expects($this->once())
            ->method('getByKrs')
            ->with($krs)
            ->willThrowException($unexpectedException);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected error during KRS lookup', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Unexpected error: Unexpected error');

        $this->handler->searchSingle($krs);
    }
}
