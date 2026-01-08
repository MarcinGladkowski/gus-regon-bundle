<?php

declare(strict_types=1);

namespace GusBundle\DTO;

final readonly class PkdCodeDTO
{
    public function __construct(
        public string $code,
        public string $name,
        public bool $isPrimary = false
    ) {
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'isPrimary' => $this->isPrimary,
        ];
    }
}
