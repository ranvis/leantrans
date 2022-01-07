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
        private array $domains,
    ) {
    }

    public function addDomain(string $domain, array $msgs): void
    {
        $this->domains[$domain] = $msgs;
    }

    public function query(string $msg, string $domain): string
    {
        return $this->domains[$domain][$msg] ?? $msg;
    }
}
