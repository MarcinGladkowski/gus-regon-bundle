<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Tests\Unit\DTO;

use MarcinGladkowski\GusBundle\DTO\AddressDTO;
use MarcinGladkowski\GusBundle\DTO\BusinessDataDTO;
use MarcinGladkowski\GusBundle\DTO\PkdCodeDTO;
use PHPUnit\Framework\TestCase;

final class BusinessDataDTOTest extends TestCase
{
    public function testConstructWithMinimalData(): void
    {
        $business = new BusinessDataDTO(
            regon: '123456785'
        );

        $this->assertEquals('123456785', $business->regon);
        $this->assertNull($business->nip);
        $this->assertNull($business->name);
    }

    public function testConstructWithFullData(): void
    {
        $address = new AddressDTO(
            street: 'Marszałkowska',
            buildingNumber: '142',
            city: 'Warszawa',
            postalCode: '00-950'
        );

        $pkdCode = new PkdCodeDTO(
            code: '62.01.Z',
            name: 'Działalność związana z oprogramowaniem',
            isPrimary: true
        );

        $business = new BusinessDataDTO(
            regon: '123456785',
            nip: '5260250274',
            name: 'Example Company Sp. z o.o.',
            legalForm: 'Spółka z ograniczoną odpowiedzialnością',
            status: 'active',
            registeredAddress: $address,
            pkdCodes: [$pkdCode]
        );

        $this->assertEquals('123456785', $business->regon);
        $this->assertEquals('5260250274', $business->nip);
        $this->assertEquals('Example Company Sp. z o.o.', $business->name);
        $this->assertEquals('active', $business->status);
        $this->assertCount(1, $business->pkdCodes);
    }

    public function testGetPrimaryPkdCode(): void
    {
        $primaryPkd = new PkdCodeDTO('62.01.Z', 'Primary activity', true);
        $secondaryPkd = new PkdCodeDTO('62.02.Z', 'Secondary activity', false);

        $business = new BusinessDataDTO(
            regon: '123456785',
            pkdCodes: [$secondaryPkd, $primaryPkd]
        );

        $this->assertEquals($primaryPkd, $business->getPrimaryPkdCode());
    }

    public function testGetPrimaryPkdCodeReturnsFirstWhenNoPrimary(): void
    {
        $pkd1 = new PkdCodeDTO('62.01.Z', 'Activity 1', false);
        $pkd2 = new PkdCodeDTO('62.02.Z', 'Activity 2', false);

        $business = new BusinessDataDTO(
            regon: '123456785',
            pkdCodes: [$pkd1, $pkd2]
        );

        $this->assertEquals($pkd1, $business->getPrimaryPkdCode());
    }

    public function testIsActiveWithActiveStatus(): void
    {
        $business = new BusinessDataDTO(
            regon: '123456785',
            status: 'active'
        );

        $this->assertTrue($business->isActive());
    }

    public function testIsActiveWithPolishStatus(): void
    {
        $business = new BusinessDataDTO(
            regon: '123456785',
            status: 'Aktywny'
        );

        $this->assertTrue($business->isActive());
    }

    public function testIsActiveWithInactiveStatus(): void
    {
        $business = new BusinessDataDTO(
            regon: '123456785',
            status: 'closed'
        );

        $this->assertFalse($business->isActive());
    }

    public function testToArray(): void
    {
        $business = new BusinessDataDTO(
            regon: '123456785',
            nip: '5260250274',
            name: 'Example Company'
        );

        $array = $business->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('123456785', $array['regon']);
        $this->assertEquals('5260250274', $array['nip']);
        $this->assertEquals('Example Company', $array['name']);
    }
}
