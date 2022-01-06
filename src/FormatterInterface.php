<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

interface FormatterInterface
{
    public function format(string $str, array $params): string;
}
