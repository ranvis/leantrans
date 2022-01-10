<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ranvis\LeanTrans\TwigTranslator
 */
class TwigTranslatorTest extends TestCase
{
    public function test__construct(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $formatter = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        new TwigTranslator($provider, $formatter);
        $this->expectException(\InvalidArgumentException::class);
        new TwigTranslator($translator, $formatter);
    }

    public function testGetTokenParsers(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $instance = new TwigTranslator($translator);
        $parsers = array_map(fn ($v) => $v::class, $instance->getTokenParsers());
        $this->assertContains(TransTokenParser::class, $parsers);
    }

    public function testGetFilters(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $instance = new TwigTranslator($translator);
        $parsers = array_map(fn ($v) => $v->getName(), $instance->getFilters());
        $this->assertContains('trans', $parsers);
        $this->assertContains('t', $parsers);
    }

    public function testTranslate(): void
    {
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $twigTranslator = new TwigTranslator($translator);
        $translator->expects($this->once())->method('translate');
        $twigTranslator->translate('msg');
    }
}
