<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Tests\Unit\Client\SearchHandler;

use MarcinGladkowski\GusBundle\Client\SearchHandler\NipSearchHandler;
use MarcinGladkowski\GusBundle\Exception\ApiAuthenticationException;
use MarcinGladkowski\GusBundle\Exception\ApiConnectionException;
use MarcinGladkowski\GusBundle\Exception\CompanyNotFoundException;
use MarcinGladkowski\GusBundle\Exception\InvalidNipException;
use MarcinGladkowski\GusBundle\Validator\NipValidator;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\SearchReport;
use GusApi\Type\Response\SearchResponseCompanyData;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class NipSearchHandlerTest extends TestCase
{
    private GusApi|MockObject $gusApi;
    private LoggerInterface|MockObject $logger;
    private NipValidator $nipValidator;
    private NipSearchHandler $handler;

    protected function setUp(): void
    {
        $this->gusApi = $this->createMock(GusApi::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->nipValidator = new NipValidator();
        
        $this->handler = new NipSearchHandler(
            $this->gusApi,
            $this->logger,
            $this->nipValidator
        );
    }

    public function testSearchSingleWithValidNipReturnsSearchReport(): void
    {
        $nip = '5260250274';
        $searchReport = new SearchReport(new SearchResponseCompanyData());
        
        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willReturn([$searchReport]);

        $result = $this->handler->searchSingle($nip);

        $this->assertInstanceOf(SearchReport::class, $result);
        $this->assertSame($searchReport, $result);
    }

    public function testSearchSingleWithInvalidNipThrowsException(): void
    {
        $invalidNip = '123456789';

        $this->expectException(InvalidNipException::class);
        $this->expectExceptionMessage("Invalid NIP number: {$invalidNip}");

        $this->handler->searchSingle($invalidNip);
    }

    public function testSearchSingleWithNonNumericNipThrowsException(): void
    {
        $invalidNip = '526-025-02-74';

        $this->expectException(InvalidNipException::class);
        $this->expectExceptionMessage("Invalid NIP number: {$invalidNip}");

        $this->handler->searchSingle($invalidNip);
    }

    public function testSearchSingleWithInvalidLengthThrowsException(): void
    {
        $invalidNip = '526025027';

        $this->expectException(InvalidNipException::class);
        $this->expectExceptionMessage("Invalid NIP number: {$invalidNip}");

        $this->handler->searchSingle($invalidNip);
    }

    public function testSearchSingleWhenNotFoundThrowsCompanyNotFoundException(): void
    {
        $nip = '5260250274';

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willThrowException(new NotFoundException('Not found'));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('NIP not found', ['NIP' => $nip]);

        $this->expectException(CompanyNotFoundException::class);
        $this->expectExceptionMessage("Business with NIP {$nip} not found");

        $this->handler->searchSingle($nip);
    }

    public function testSearchSingleWithInvalidApiKeyThrowsApiAuthenticationException(): void
    {
        $nip = '5260250274';

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willThrowException(new InvalidUserKeyException('Invalid key'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Invalid API key', $this->anything());

        $this->expectException(ApiAuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->handler->searchSingle($nip);
    }

    public function testSearchSingleWithSoapFaultThrowsApiConnectionException(): void
    {
        $nip = '5260250274';

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willThrowException(new \SoapFault('Server', 'Connection error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('SOAP connection error', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Failed to connect to GUS API');

        $this->handler->searchSingle($nip);
    }

    public function testSearchSingleWithMultipleResultsThrowsException(): void
    {
        $nip = '5260250274';
        $searchReport1 = new SearchReport(new SearchResponseCompanyData());
        $searchReport2 = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willReturn([$searchReport1, $searchReport2]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Multiple results found when single expected');

        $this->handler->searchSingle($nip);
    }

    public function testSearchSingleWithEmptyResultReturnsEmptySearchReport(): void
    {
        $nip = '5260250274';

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willReturn([]);

        $result = $this->handler->searchSingle($nip);

        $this->assertInstanceOf(SearchReport::class, $result);
    }

    public function testSearchSingleWithUnexpectedExceptionThrowsApiConnectionException(): void
    {
        $nip = '5260250274';
        $unexpectedException = new \RuntimeException('Unexpected error');

        $this->gusApi->expects($this->once())
            ->method('getByNip')
            ->with($nip)
            ->willThrowException($unexpectedException);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected error during NIP lookup', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Unexpected error: Unexpected error');

        $this->handler->searchSingle($nip);
    }
}
