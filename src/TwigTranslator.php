<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use Twig\TwigFilter;

/**
 * LeanTrans Twig extension
 */
class TwigTranslator extends \Twig\Extension\AbstractExtension implements TranslatorInterface
{
    private TranslatorInterface $translator;

    /**
     * Instantiate Twig Translator extension.
     *
     * @param TranslatorInterface|ProviderInterface $translator The translator or message provider instance
     * @param FormatterInterface|null $formatter
     * The optional string formatter instance when the message provider is specified
     */
    public function __construct(TranslatorInterface|ProviderInterface $translator, ?FormatterInterface $formatter = null)
    {
        if ($translator instanceof ProviderInterface) {
            $translator = new Translator($translator, $formatter);
        } elseif ($formatter !== null) {
            throw new \InvalidArgumentException('formatter cannot be specified with translator');
        }
        $this->translator = $translator;
    }

    /**
     * @internal
     */
    public function getTokenParsers()
    {
        return [
            new TransTokenParser(),
        ];
    }

    /**
     * @internal
     */
    public function getFilters()
    {
        return [
            new TwigFilter('trans', $translateCb = [$this->translator, 'translate']),
            new TwigFilter('t', $translateCb),
        ];
    }

    /**
     * @see Translator::translate()
     */
    public function translate(string $msg, ?array $params = null): string
    {
        return $this->translator->translate($msg, $params);
    }

    /**
     * @see Translator::translateWithDomain()
     */
    public function translateWithDomain(string $msg, string $domain, ?array $params = null): string
    {
        return $this->translator->translateWithDomain($msg, $domain, $params);
    }

    /**
     * Get the actual translator instance.
     *
     * It is guaranteed that $this->getTranslator()->translate() does exactly the same as $this->translate().
     *
     * @return TranslatorInterface Translator instance.
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
