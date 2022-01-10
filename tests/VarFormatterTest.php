<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ranvis\LeanTrans\VarFormatter
 */
class VarFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $instance = new VarFormatter();
        $this->assertSame('Hello, world!', $instance->format('Hello, %name%!', ['name' => 'world']));
        $this->assertSame('Hello, !', $instance->format('Hello, %name%!', ['world' => 'world']));
        $this->assertSame('100%', $instance->format('100%%', ['' => 'error']));
        $this->assertSame('A B AB', $instance->format('%a% %b% %a%%b%', ['a' => 'A', 'b' => 'B']));
        $this->assertSame('Not a %var!%', $instance->format('Not a %var!%', ['var!' => 'error']));
        $this->assertSame('123', $instance->format('%str%', ['str' => 123]));
    }
}
