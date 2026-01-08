<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit\DTO;

use GusBundle\DTO\PkdCodeDTO;
use PHPUnit\Framework\TestCase;

final class PkdCodeDTOTest extends TestCase
{
    public function testConstruct(): void
    {
        $pkd = new PkdCodeDTO(
            code: '62.01.Z',
            name: 'Działalność związana z oprogramowaniem',
            isPrimary: true
        );

        $this->assertEquals('62.01.Z', $pkd->code);
        $this->assertEquals('Działalność związana z oprogramowaniem', $pkd->name);
        $this->assertTrue($pkd->isPrimary);
    }

    public function testConstructWithDefaultIsPrimary(): void
    {
        $pkd = new PkdCodeDTO(
            code: '62.02.Z',
            name: 'Działalność związana z doradztwem w zakresie informatyki'
        );

        $this->assertFalse($pkd->isPrimary);
    }

    public function testToArray(): void
    {
        $pkd = new PkdCodeDTO(
            code: '62.01.Z',
            name: 'Działalność związana z oprogramowaniem',
            isPrimary: true
        );

        $array = $pkd->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('62.01.Z', $array['code']);
        $this->assertEquals('Działalność związana z oprogramowaniem', $array['name']);
        $this->assertTrue($array['isPrimary']);
    }
}
