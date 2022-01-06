<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

interface TranslatorInterface
{
    public function translate(string $msg, ?array $params = null): string;
}
