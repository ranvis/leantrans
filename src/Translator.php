<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class Translator implements TranslatorInterface
{
    /**
     * Instantiate Translator.
     *
     * @param ProviderInterface $provider The message provider instance
     * @param FormatterInterface|null $formatter The optional string formatter instance
     */
    public function __construct(
        private ProviderInterface $provider,
        private ?FormatterInterface $formatter = null,
    ) {
    }

    /**
     * Translate message using string provider.
     *
     * If $params is specified, the message is formatted using string formatter.
     *
     * @param string $msg
     * The string to be translated.
     *
     * The string may be prefixed with ".DOMAIN.".
     * The default domain is an empty string (same as "..".)
     *
     * @param array|null $params Parameters to be inserted into the message.
     * @return string A translated string.
     */
    public function translate(string $msg, ?array $params = null): string
    {
        [$domain, $msg] = static::splitDomain($msg);
        return $this->translateWithDomain($msg, $domain, $params);
    }

    /**
     * Translate message using string provider.
     *
     * If $params is specified, the message is formatted using string formatter.
     *
     * @param string $domain The domain of the message.
     * @param string $msg The string to be translated.
     * @param array|null $params Parameters to be inserted into the message.
     * @return string A translated string.
     */
    public function translateWithDomain(string $msg, string $domain, ?array $params = null): string
    {
        $msg = $this->provider->query($msg, $domain);
        if ($params === null) {
            return $msg;
        }
        return $this->formatter?->format($msg, $params) ?? $msg;
    }

    final public static function splitDomain(string $str): array
    {
        $domain = '';
        if ($str !== '' && $str[0] === '.') {
            if (\preg_match('/\A\.([\w-]*)\./', $str, $m)) {
                $domain = $m[1];
                $str = \substr($str, strlen($m[0]));
            }
        }
        return [$domain, $str];
    }
}
