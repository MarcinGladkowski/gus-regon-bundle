<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\DTO;

final readonly class AddressDTO
{
    public function __construct(
        public ?string $street = null,
        public ?string $buildingNumber = null,
        public ?string $apartmentNumber = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $province = null,
        public ?string $county = null,
        public ?string $municipality = null,
        public ?string $country = 'PL'
    ) {
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->street ? $this->street . ' ' . $this->buildingNumber : $this->buildingNumber,
            $this->apartmentNumber ? '/' . $this->apartmentNumber : null,
            $this->postalCode && $this->city ? $this->postalCode . ' ' . $this->city : $this->city,
        ]);

        return implode(', ', $parts);
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'buildingNumber' => $this->buildingNumber,
            'apartmentNumber' => $this->apartmentNumber,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'province' => $this->province,
            'county' => $this->county,
            'municipality' => $this->municipality,
            'country' => $this->country,
        ];
    }
}
