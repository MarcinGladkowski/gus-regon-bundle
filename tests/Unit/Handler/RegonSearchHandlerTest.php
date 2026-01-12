<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit\Handler;

use GusBundle\Collection\SearchReportCollection;
use GusBundle\Handler\RegonSearchHandler;
use GusBundle\Exception\ApiAuthenticationException;
use GusBundle\Exception\ApiConnectionException;
use GusBundle\Exception\CompanyNotFoundException;
use GusBundle\Exception\InvalidRegonException;
use GusBundle\Validator\RegonValidator;
use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\SearchReport;
use GusApi\Type\Response\SearchResponseCompanyData;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class RegonSearchHandlerTest extends TestCase
{
    private GusApi $gusApi;
    private LoggerInterface $logger;
    private RegonValidator $regonValidator;
    private RegonSearchHandler $handler;

    protected function setUp(): void
    {
        $this->gusApi = $this->createMock(GusApi::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->regonValidator = new RegonValidator();

        $this->handler = new RegonSearchHandler(
            $this->gusApi,
            $this->logger,
            $this->regonValidator
        );
    }

    public function testSearchSingleWithValid9DigitRegonReturnsSearchReport(): void
    {
        $regon = '123456785';
        $searchReport = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willReturn([$searchReport]);

        $result = $this->handler->search($regon);

        $this->assertInstanceOf(SearchReportCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame($searchReport, $result->first());
    }

    public function testSearchSingleWithValid14DigitRegonReturnsSearchReport(): void
    {
        $regon = '12345678512347';
        $searchReport = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willReturn([$searchReport]);

        $result = $this->handler->search($regon);

        $this->assertInstanceOf(SearchReportCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame($searchReport, $result->first());
    }

    public function testSearchSingleWithInvalidRegonChecksumThrowsException(): void
    {
        $invalidRegon = '123456786';

        $this->expectException(InvalidRegonException::class);
        $this->expectExceptionMessage("Invalid REGON number: {$invalidRegon}");

        $this->handler->search($invalidRegon);
    }

    public function testSearchSingleWithNonNumericRegonThrowsException(): void
    {
        $invalidRegon = '123-456-785';

        $this->expectException(InvalidRegonException::class);
        $this->expectExceptionMessage("Invalid REGON number: {$invalidRegon}");

        $this->handler->search($invalidRegon);
    }

    public function testSearchSingleWithInvalidLengthThrowsException(): void
    {
        $invalidRegon = '12345678';

        $this->expectException(InvalidRegonException::class);
        $this->expectExceptionMessage("Invalid REGON number: {$invalidRegon}");

        $this->handler->search($invalidRegon);
    }

    public function testSearchSingleWhenNotFoundThrowsCompanyNotFoundException(): void
    {
        $regon = '123456785';

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willThrowException(new NotFoundException('Not found'));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('REGON not found', ['REGON' => $regon]);

        $this->expectException(CompanyNotFoundException::class);
        $this->expectExceptionMessage("Business with REGON {$regon} not found");

        $this->handler->search($regon);
    }

    public function testSearchSingleWithInvalidApiKeyThrowsApiAuthenticationException(): void
    {
        $regon = '123456785';

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willThrowException(new InvalidUserKeyException('Invalid key'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Invalid API key', $this->anything());

        $this->expectException(ApiAuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->handler->search($regon);
    }

    public function testSearchSingleWithSoapFaultThrowsApiConnectionException(): void
    {
        $regon = '123456785';

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willThrowException(new \SoapFault('Server', 'Connection error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('SOAP connection error', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Failed to connect to GUS API');

        $this->handler->search($regon);
    }

    public function testSearchSingleWithMultipleResultsReturnsCollection(): void
    {
        $regon = '123456785';
        $searchReport1 = new SearchReport(new SearchResponseCompanyData());
        $searchReport2 = new SearchReport(new SearchResponseCompanyData());

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willReturn([$searchReport1, $searchReport2]);

        $result = $this->handler->search($regon);

        $this->assertInstanceOf(SearchReportCollection::class, $result);
        $this->assertCount(2, $result);
    }

    public function testSearchSingleWithEmptyResultReturnsEmptySearchReport(): void
    {
        $regon = '123456785';

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willReturn([]);

        $result = $this->handler->search($regon);

        $this->assertInstanceOf(SearchReportCollection::class, $result);
        $this->assertCount(0, $result);
    }

    public function testSearchSingleWithUnexpectedExceptionThrowsApiConnectionException(): void
    {
        $regon = '123456785';
        $unexpectedException = new \RuntimeException('Unexpected error');

        $this->gusApi->expects($this->once())
            ->method('getByRegon')
            ->with($regon)
            ->willThrowException($unexpectedException);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected error during REGON lookup', $this->anything());

        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage('Unexpected error: Unexpected error');

        $this->handler->search($regon);
    }
}
