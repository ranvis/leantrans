<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

interface ProviderInterface
{
    public function query(string $msg, string $domain): string;
}
