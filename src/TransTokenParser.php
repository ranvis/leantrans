<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Token;

/**
 * Trans token parser
 *
 *  {% trans %}message{% endtrans %}
 *  {% trans with {param: value} %}message with %param%{% endtrans %}
 *
 * @internal
 */
class TransTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    /**
     * @inheritDoc
     */
    public function parse(Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $params = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $params = $parser->getExpressionParser()->parseExpression();
            if (!($params instanceof ArrayExpression || $params instanceof NameExpression)) {
                throw new SyntaxError('A parameter of a trans tag must be of type array/hash or variable of it.', $params->getTemplateLine(), $params->getSourceContext());
            }
        }
        $stream->expect(Token::BLOCK_END_TYPE);
        $msg = $parser->subparse([$this, 'isEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);
        return new TransNode($msg, $params, $token->getLine(), $this->getTag());
    }

    public function isEnd(Token $token): bool
    {
        return $token->test('endtrans');
    }

    /**
     * @inheritDoc
     */
    public function getTag()
    {
        return 'trans';
    }
}
