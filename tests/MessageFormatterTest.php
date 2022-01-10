<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;
use Ranvis\LeanTrans\TestUtil\SilentMessageFormatter;

/**
 * @covers \Ranvis\LeanTrans\MessageFormatter
 */
class MessageFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $instance = new MessageFormatter('en-US');
        $msg = $instance->format('{count, plural, =0 {no items} =1 {# item} other {# items}}', ['count' => 12345]);
        $this->assertSame('12,345 items', $msg);
    }

    public function testFormatError(): void
    {
        $instance = new SilentMessageFormatter('en-US');
        $this->assertSame(0, $instance->warned);
        $this->assertSame('', $instance->format('{count', ['count' => 12345]));
        $this->assertSame(1, $instance->warned);
        $this->assertSame('', $instance->format('{str}', ['str' => "\xffinvalid UTF-8"]));
        $this->assertSame(2, $instance->warned);
    }

    public function testFormatErrorNotice(): void
    {
        $instance = new MessageFormatter('en-US');
        $this->expectNotice();
        $this->assertSame('', $instance->format('{count', ['count' => 12345]));
    }

    public function testFormatErrorReturns(): void
    {
        $savedHandler = set_error_handler(fn () => true);
        $instance = new MessageFormatter('en-US');
        $this->assertSame('', $instance->format('{count', ['count' => 12345]));
        set_error_handler($savedHandler);
    }
}
