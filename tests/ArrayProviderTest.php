<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ranvis\LeanTrans\ArrayProvider
 */
class ArrayProviderTest extends TestCase
{
    public function test__construct()
    {
        $maps = [
            '' => ['msg' => 'default'],
            'a' => ['msg' => 'A'],
        ];
        $instance = new ArrayProvider($maps);
        $this->assertSame($maps['']['msg'], $instance->query('msg', ''));
        $this->assertSame($maps['a']['msg'], $instance->query('msg', 'a'));
        $this->assertSame('none', $instance->query('none', ''));
        $this->assertSame('msg', $instance->query('msg', 'none'));
    }

    public function testAddMap(): void
    {
        $instance = new ArrayProvider([]);
        $this->assertSame('msg', $instance->query('msg', ''));
        $instance->addDomain('', ['msg' => 'default']);
        $this->assertSame('default', $instance->query('msg', ''));
        $instance->addDomain('', []);
        $this->assertSame('msg', $instance->query('msg', ''));
    }
}
