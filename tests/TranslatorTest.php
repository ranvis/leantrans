<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ranvis\LeanTrans\Translator
 */
class TranslatorTest extends TestCase
{
    public function testTranslate(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $instance = new Translator($provider, $formatter);
        $provider->method('query')->willReturn('translated');
        $formatter->method('format')->willReturn('formatted');
        $this->assertSame('translated', $instance->translate('msg'));
        $this->assertSame('formatted', $instance->translate('msg', []));
    }

    public function testSplitDomain(): void
    {
        $this->assertSame(['', ''], Translator::splitDomain(''));
        $this->assertSame(['', '.'], Translator::splitDomain('.'));
        $this->assertSame(['', ''], Translator::splitDomain('..'));
        $this->assertSame(['', '.'], Translator::splitDomain('...'));
        $this->assertSame(['', 'msg'], Translator::splitDomain('msg'));
        $this->assertSame(['domain', ''], Translator::splitDomain('.domain.'));
        $this->assertSame(['domain', 'msg.'], Translator::splitDomain('.domain.msg.'));
        $this->assertSame(['', 'msg.'], Translator::splitDomain('..msg.'));
        $this->assertSame(['', '.msg also.'], Translator::splitDomain('.msg also.'));
    }
}
