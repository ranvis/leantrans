<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;
use Twig\Error\SyntaxError;

/**
 * @covers \Ranvis\LeanTrans\TwigTranslator
 * @covers \Ranvis\LeanTrans\TransTokenParser
 */
class TwigTest extends TestCase
{
    public function testTemplate(): void
    {
        $template = <<<'END'
            filter:{{ 'message %var%'|trans({var}) }} end
            tag: {% trans %}message{% endtrans %} end
            tag: {% trans with {var} %}message %var%{% endtrans %} end
            END;
        $msgMap = [
            ['message', '', 'translated'],
            ['message %var%', '', 'message %var%'],
        ];
        $vars = ['var' => '<br>'];
        $expected = <<<'END'
            filter:message &lt;br&gt; end
            tag: translated end
            tag: message <br> end
            END;
        $actual = $this->render($template, $msgMap, $vars);
        $this->assertSame($expected, $actual);
    }

    public function testException(): void
    {
        $template = <<<'END'
            tag: {% trans with 123 %}message %var%{% endtrans %} end
            END;
        $vars = ['var' => '<br>'];
        $this->expectException(SyntaxError::class);
        $this->render($template, [], $vars);
    }

    public function testTranslateWithDomain(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $provider->method('query')->willReturnMap([['message', 'domain', 'translated']]);
        $formatter = new VarFormatter();
        $twigTranslator = new TwigTranslator($provider, $formatter);
        $this->assertSame('translated', $twigTranslator->translateWithDomain('message', 'domain'));
    }

    private function render(string $template, array $msgMap, array $vars = []): string
    {
        $loader = new \Twig\Loader\ArrayLoader(['test' => $template]);
        $twig = new \Twig\Environment($loader);
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMock();
        $provider->method('query')->willReturnMap($msgMap);
        $formatter = new VarFormatter();
        $twig->addExtension(new TwigTranslator($provider, $formatter));
        return $twig->render('test', $vars);
    }
}
