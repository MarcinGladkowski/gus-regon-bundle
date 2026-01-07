<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Validator;

final class RegonValidator
{
    public function validate(string $regon): bool
    {
        if (!$this->isValidLength($regon)) {
            return false;
        }

        if (!ctype_digit($regon)) {
            return false;
        }

        return $this->isValidChecksum($regon);
    }

    private function isValidLength(string $regon): bool
    {
        $length = strlen($regon);
        return $length === 9 || $length === 14;
    }

    private function isValidChecksum(string $regon): bool
    {
        $length = strlen($regon);

        if ($length === 9) {
            return $this->validateNineDigitChecksum($regon);
        }

        if ($length === 14) {
            return $this->validateFourteenDigitChecksum($regon);
        }

        return false;
    }

    private function validateNineDigitChecksum(string $regon): bool
    {
        $weights = [8, 9, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += (int)$regon[$i] * $weights[$i];
        }

        $checksum = $sum % 11;
        if ($checksum === 10) {
            $checksum = 0;
        }

        return $checksum === (int)$regon[8];
    }

    private function validateFourteenDigitChecksum(string $regon): bool
    {
        if (!$this->validateNineDigitChecksum(substr($regon, 0, 9))) {
            return false;
        }

        $weights = [2, 4, 8, 5, 0, 9, 7, 3, 6, 1, 2, 4, 8];
        $sum = 0;

        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$regon[$i] * $weights[$i];
        }

        $checksum = $sum % 11;
        if ($checksum === 10) {
            $checksum = 0;
        }

        return $checksum === (int)$regon[13];
    }
}
