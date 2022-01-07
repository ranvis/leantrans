<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\TextNode;

/**
 * @internal
 */
class TransNode extends Node
{
    public function __construct($msg, ?\Twig\Node\Expression\AbstractExpression $params, $line, $tag = null)
    {
        parent::__construct(['msg' => $msg], ['params' => $params], $line, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $msg = $this->getNode('msg');
        $isText = $msg instanceof TextNode;
        if (!$isText) {
            $this->subcompileTo('$tmp', $msg, $compiler);
        }
        $compiler->write('$leanTransTranslator ??= ' . $this->getExtension($compiler) . "->getTranslator();\n");
        $compiler->write('echo $leanTransTranslator->');
        if ($isText) {
            $msg = $msg->getAttribute('data');
            [$domain, $msg] = Translator::splitDomain($msg);
            $compiler->write('translateWithDomain(')
                ->string($msg)->raw(', ')->string($domain)
            ;
        } else {
            $compiler->raw('translate($tmp');
        }
        $params = $this->getAttribute('params');
        if ($params !== null) {
            $compiler->raw(', ')->subcompile($params);
        }
        $compiler->raw(");\n");
        if (!$isText) {
            $compiler->write('$tmp = null;' . "\n");
        }
    }

    private function subcompileTo(string $var, Node $node, Compiler $compiler): void
    {
        // the following buffer code is based on twigphp/Twig /src/Node/SetNode.php © 2019 Fabien Potencier et al. @license <https://opensource.org/licenses/BSD-3-Clause>
        if ($compiler->getEnvironment()->isDebug()) {
            // twigphp/Twig #3059
            $compiler->write("ob_start();\n");
        } else {
            $compiler->write("ob_start(fn () => '');\n");
        }
        $compiler->subcompile($node);
        $compiler->write("$var = (($var = ob_get_clean()) === '') ? '' : new Markup($var, \$this->env->getCharset());\n");
    }

    private function getExtension(Compiler $compiler): string
    {
        $class = TwigTranslator::class;
        // the following extension test code is based on twigphp/Twig /src/Node/Expression/CallExpression.php © 2020 Fabien Potencier et al. @license <https://opensource.org/licenses/BSD-3-Clause>
        if (!$compiler->getEnvironment()->hasExtension($class)) {
            // Compile a non-optimized call to trigger a \Twig\Error\RuntimeError, which cannot be a compile-time error
            return '$this->env->getExtension(\'' . $class . '\')';
        }
        return '$this->extensions[\'' . ltrim($class, '\\') . '\']';
    }
}
