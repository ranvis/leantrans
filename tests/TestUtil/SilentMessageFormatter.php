<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans\TestUtil;

use Ranvis\LeanTrans\MessageFormatter;

class SilentMessageFormatter extends MessageFormatter
{
    public int $warned = 0;

    protected function warn(string $errorMsg, array $data): void
    {
        $this->warned++;
    }
}
