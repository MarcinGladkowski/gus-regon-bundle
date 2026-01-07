<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Tests\Unit\DTO;

use MarcinGladkowski\GusBundle\DTO\AddressDTO;
use PHPUnit\Framework\TestCase;

final class AddressDTOTest extends TestCase
{
    public function testConstructWithAllFields(): void
    {
        $address = new AddressDTO(
            street: 'Marszałkowska',
            buildingNumber: '142',
            apartmentNumber: '5',
            city: 'Warszawa',
            postalCode: '00-950',
            province: 'mazowieckie',
            county: 'Warszawa',
            municipality: 'Warszawa',
            country: 'PL'
        );

        $this->assertEquals('Marszałkowska', $address->street);
        $this->assertEquals('142', $address->buildingNumber);
        $this->assertEquals('5', $address->apartmentNumber);
        $this->assertEquals('Warszawa', $address->city);
        $this->assertEquals('00-950', $address->postalCode);
        $this->assertEquals('PL', $address->country);
    }

    public function testGetFullAddress(): void
    {
        $address = new AddressDTO(
            street: 'Marszałkowska',
            buildingNumber: '142',
            apartmentNumber: '5',
            city: 'Warszawa',
            postalCode: '00-950'
        );

        $this->assertEquals('Marszałkowska 142, /5, 00-950 Warszawa', $address->getFullAddress());
    }

    public function testGetFullAddressWithoutApartment(): void
    {
        $address = new AddressDTO(
            street: 'Marszałkowska',
            buildingNumber: '142',
            city: 'Warszawa',
            postalCode: '00-950'
        );

        $this->assertEquals('Marszałkowska 142, 00-950 Warszawa', $address->getFullAddress());
    }

    public function testToArray(): void
    {
        $address = new AddressDTO(
            street: 'Marszałkowska',
            buildingNumber: '142',
            city: 'Warszawa',
            postalCode: '00-950'
        );

        $array = $address->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Marszałkowska', $array['street']);
        $this->assertEquals('142', $array['buildingNumber']);
        $this->assertEquals('Warszawa', $array['city']);
        $this->assertEquals('00-950', $array['postalCode']);
        $this->assertEquals('PL', $array['country']);
    }
}
