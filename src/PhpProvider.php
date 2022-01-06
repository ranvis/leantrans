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
        private array $maps,
    ) {
    }

    public static function compile(array $map): string
    {
        return '<?php return ' . var_export($map, true) . ';';
    }

    public function addMap(string $domain, string $path): void
    {
        $this->maps[$domain] = $path;
    }

    protected function loadMap(string $path): array
    {
        $map = require($path);
        if (!\is_array($map)) {
            throw new \UnexpectedValueException('PHP map should return an array of translations: ' . $path);
        }
        return $map;
    }

    public function query(string $msg, string $domain): string
    {
        $map = $this->maps[$domain] ?? null;
        if (!isset($map)) {
            return $msg;
        }
        if (!\is_array($map)) {
            $this->maps[$domain] = $map = $this->loadMap($map);
        }
        return $map[$msg] ?? $msg;
    }
}
