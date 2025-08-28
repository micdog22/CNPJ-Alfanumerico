<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use MicDog\CnpjAlfa\CnpjAlfa;

final class CnpjAlfaTest extends TestCase
{
    public function testNormalize(): void
    {
        $this->assertSame('12ABC34501DE35', CnpjAlfa::normalize('12a.bc-345/01de-35'));
    }

    public function testValidTraditionalSamples(): void
    {
        $this->assertTrue(CnpjAlfa::validate('00.000.000/0001-91'));
        $this->assertTrue(CnpjAlfa::validate('59.952.259/0001-85'));
    }

    public function testValidAlfaSamples(): void
    {
        $this->assertTrue(CnpjAlfa::validate('12ABC34501DE35'));
        $this->assertTrue(CnpjAlfa::validate('12aBc34501DE35'));
    }

    public function testInvalidLastDigits(): void
    {
        $this->assertFalse(CnpjAlfa::validate('12ABC34501DE00'));
    }

    public function testComputeDv(): void
    {
        [$d1, $d2] = CnpjAlfa::computeDv('12ABC34501DE');
        $this->assertIsInt($d1);
        $this->assertIsInt($d2);
        $this->assertTrue(CnpjAlfa::validate('12ABC34501DE' . $d1 . $d2));
    }

    public function testFormat(): void
    {
        $this->assertSame('12.ABC.345/01DE-35', CnpjAlfa::format('12ABC34501DE35'));
    }
}
