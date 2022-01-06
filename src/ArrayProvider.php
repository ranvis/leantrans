<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class ArrayProvider implements ProviderInterface
{
    public function __construct(
        private array $maps,
    ) {
    }

    public function addMap(string $domain, array $map): void
    {
        $this->maps[$domain] = $map;
    }

    public function query(string $msg, string $domain): string
    {
        return $this->maps[$domain][$msg] ?? $msg;
    }
}
