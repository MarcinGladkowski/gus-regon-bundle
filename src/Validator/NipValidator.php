<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Validator;

final class NipValidator
{
    public function validate(string $nip): bool
    {
        if (!$this->isValidLength($nip)) {
            return false;
        }

        if (!ctype_digit($nip)) {
            return false;
        }

        return $this->isValidChecksum($nip);
    }

    private function isValidLength(string $nip): bool
    {
        return strlen($nip) === 10;
    }

    private function isValidChecksum(string $nip): bool
    {
        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$nip[$i] * $weights[$i];
        }

        $checksum = $sum % 11;
        if ($checksum === 10) {
            $checksum = 0;
        }

        return $checksum === (int)$nip[9];
    }
}
