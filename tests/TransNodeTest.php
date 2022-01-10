<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Node\TextNode;
use Twig\Test\NodeTestCase;

/**
 * @covers \Ranvis\LeanTrans\TransNode
 */
class TransNodeTest extends NodeTestCase
{
    public function test__construct(): void
    {
        $msg = new TextNode('msg', 1);
        $params = new NameExpression('params', 1);
        $node = new TransNode($msg, $params, 1);
        $this->assertSame($msg, $node->getNode('msg'));
        $this->assertSame($params, $node->getAttribute('params'));
    }

    protected function getEnvironment($options = []): Environment
    {
        $env = new Environment(new ArrayLoader([]), $options);
        $translator = $this->getMockBuilder(Translator::class)->disableOriginalConstructor()->getMock();
        $env->addExtension(new TwigTranslator($translator));
        return $env;
    }

    public function getTests(): array
    {
        $msg = new TextNode('msg', 1);
        $msgVar = fn ($ln) => new NameExpression('msgVar', $ln);
        $params = fn ($ln) => new NameExpression('params', $ln);
        $envNoExt = parent::getEnvironment();
        return [
            ($ln = __LINE__) => [new TransNode($msg, null, $ln), "// line $ln\n" . <<<'END'
                $leanTransTranslator ??= $this->extensions['Ranvis\LeanTrans\TwigTranslator']->getTranslator();
                echo $leanTransTranslator->translateWithDomain("msg", "");
                END
            ],
            // the extension is not installed
            ($ln = __LINE__) => [new TransNode($msg, null, $ln), "// line $ln\n" . <<<'END'
                $leanTransTranslator ??= $this->env->getExtension('Ranvis\LeanTrans\TwigTranslator')->getTranslator();
                echo $leanTransTranslator->translateWithDomain("msg", "");
                END, $envNoExt
            ],
            // message with parameters
            ($ln = __LINE__) => [new TransNode($msg, $params($ln), $ln), "// line $ln\n" . <<<'END'
                $leanTransTranslator ??= $this->extensions['Ranvis\LeanTrans\TwigTranslator']->getTranslator();
                echo $leanTransTranslator->translateWithDomain("msg", "", ($context["params"] ?? null));
                END
            ],
            // expression message
            ($ln = __LINE__) => [new TransNode(new PrintNode($msgVar($ln), $ln), null, $ln), "// line $ln\n" . <<<'END'
                ob_start(fn () => '');
                echo ($context["msgVar"] ?? null);
                $tmp = (($tmp = ob_get_clean()) === '') ? '' : new Markup($tmp, $this->env->getCharset());
                $leanTransTranslator ??= $this->extensions['Ranvis\LeanTrans\TwigTranslator']->getTranslator();
                echo $leanTransTranslator->translate($tmp);
                $tmp = null;
                END
            ],
            // debug on
            ($ln = __LINE__) => [new TransNode(new PrintNode($msgVar($ln), $ln), null, $ln), "// line $ln\n" . <<<'END'
                ob_start();
                echo ($context["msgVar"] ?? null);
                $tmp = (($tmp = ob_get_clean()) === '') ? '' : new Markup($tmp, $this->env->getCharset());
                $leanTransTranslator ??= $this->extensions['Ranvis\LeanTrans\TwigTranslator']->getTranslator();
                echo $leanTransTranslator->translate($tmp);
                $tmp = null;
                END, $this->getEnvironment(['debug' => true])
            ],
        ];
    }
}
