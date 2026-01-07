<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Tests\Unit\Validator;

use MarcinGladkowski\GusBundle\Validator\NipValidator;
use PHPUnit\Framework\TestCase;

final class NipValidatorTest extends TestCase
{
    private NipValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new NipValidator();
    }

    public function testValidNip(): void
    {
        $this->assertTrue($this->validator->validate('5260250274'));
    }

    public function testInvalidNipChecksum(): void
    {
        $this->assertFalse($this->validator->validate('5260250275'));
    }

    public function testInvalidNipLength(): void
    {
        $this->assertFalse($this->validator->validate('526025027'));
        $this->assertFalse($this->validator->validate('52602502744'));
    }

    public function testNipWithDashesIsInvalid(): void
    {
        $this->assertFalse($this->validator->validate('526-025-02-74'));
    }

    public function testNipWithSpacesIsInvalid(): void
    {
        $this->assertFalse($this->validator->validate('526 025 02 74'));
    }

    public function testMultipleValidNips(): void
    {
        $validNips = [
            '5260250274',
            '1234563218',
        ];

        foreach ($validNips as $nip) {
            $this->assertTrue($this->validator->validate($nip), "NIP {$nip} should be valid");
        }
    }
}
