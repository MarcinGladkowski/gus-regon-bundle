<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\DTO;

final readonly class BusinessDataDTO
{
    /**
     * @param PkdCodeDTO[] $pkdCodes
     */
    public function __construct(
        public string $regon,
        public ?string $nip = null,
        public ?string $name = null,
        public ?string $shortName = null,
        public ?string $legalForm = null,
        public ?string $status = null,
        public ?AddressDTO $registeredAddress = null,
        public ?AddressDTO $businessAddress = null,
        public array $pkdCodes = [],
        public ?string $companySize = null,
        public ?int $employeeCount = null,
        public ?\DateTimeInterface $registrationDate = null,
        public ?\DateTimeInterface $startDate = null,
        public ?\DateTimeInterface $lastUpdateDate = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?array $rawData = null
    ) {
    }

    public function getPrimaryPkdCode(): ?PkdCodeDTO
    {
        foreach ($this->pkdCodes as $pkdCode) {
            if ($pkdCode->isPrimary) {
                return $pkdCode;
            }
        }

        return $this->pkdCodes[0] ?? null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'Aktywny';
    }

    public function toArray(): array
    {
        return [
            'regon' => $this->regon,
            'nip' => $this->nip,
            'name' => $this->name,
            'shortName' => $this->shortName,
            'legalForm' => $this->legalForm,
            'status' => $this->status,
            'registeredAddress' => $this->registeredAddress?->toArray(),
            'businessAddress' => $this->businessAddress?->toArray(),
            'pkdCodes' => array_map(fn(PkdCodeDTO $pkd) => $pkd->toArray(), $this->pkdCodes),
            'companySize' => $this->companySize,
            'employeeCount' => $this->employeeCount,
            'registrationDate' => $this->registrationDate?->format('Y-m-d'),
            'startDate' => $this->startDate?->format('Y-m-d'),
            'lastUpdateDate' => $this->lastUpdateDate?->format('Y-m-d'),
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
        ];
    }
}
