<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class VarFormatter implements FormatterInterface
{
    public function format(string $str, array $params): string
    {
        // regex has no /u flag as per the extension's spec.
        return \preg_replace_callback('/%([\w-]*?)%/', function (array $m) use ($params): string {
            if ($m[1] === '') {
                return '%';  // %% => %
            }
            return $params[$m[1]] ?? '';
        }, $str) ?? '';
    }
}
