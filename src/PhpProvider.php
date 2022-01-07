<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class PhpProvider implements ProviderInterface
{
    public function __construct(
        private array $domains,
    ) {
    }

    public static function compile(array $msgs): string
    {
        return '<?php return ' . var_export($msgs, true) . ';';
    }

    public function addDomain(string $domain, string $path): void
    {
        $this->domains[$domain] = $path;
    }

    protected function loadDomain(string $path): array
    {
        $msgs = require($path);
        if (!\is_array($msgs)) {
            throw new \UnexpectedValueException('PHP script should return an array of translations: ' . $path);
        }
        return $msgs;
    }

    public function query(string $msg, string $domain): string
    {
        $msgs = $this->domains[$domain] ?? null;
        if (!isset($msgs)) {
            return $msg;
        }
        if (!\is_array($msgs)) {
            $this->domains[$domain] = $msgs = $this->loadDomain($msgs);
        }
        return $msgs[$msg] ?? $msg;
    }
}
