<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Tests\Unit\Validator;

use MarcinGladkowski\GusBundle\Validator\RegonValidator;
use PHPUnit\Framework\TestCase;

final class RegonValidatorTest extends TestCase
{
    private RegonValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RegonValidator();
    }

    public function testValidNineDigitRegon(): void
    {
        $this->assertTrue($this->validator->validate('123456785'));
    }

    public function testValidFourteenDigitRegon(): void
    {
        $this->assertTrue($this->validator->validate('12345678512347'));
    }

    public function testInvalidNineDigitRegonChecksum(): void
    {
        $this->assertFalse($this->validator->validate('123456784'));
    }

    public function testInvalidFourteenDigitRegonChecksum(): void
    {
        $this->assertFalse($this->validator->validate('12345678512348'));
    }

    public function testInvalidRegonLength(): void
    {
        $this->assertFalse($this->validator->validate('12345'));
        $this->assertFalse($this->validator->validate('1234567890123'));
    }

    public function testRegonWithDashesIsInvalid(): void
    {
        $this->assertFalse($this->validator->validate('12-345-67-85'));
    }

    public function testRegonWithSpacesIsInvalid(): void
    {
        $this->assertFalse($this->validator->validate('12 345 67 85'));
    }
}
