<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class MessageFormatter implements FormatterInterface
{
    private array $cacheMap = [];

    public function __construct(
        private string $locale,
        private bool $cache = true,
    ) {
    }

    public function format(string $str, array $params): string
    {
        $msgFmt = $this->cacheMap[$str] ?? null;
        if ($msgFmt === null) {
            try {
                $msgFmt = new \MessageFormatter($this->locale, $str);
            } catch (\IntlException $e) {
                $this->warn('Failed to instantiate MessageFormatter', ['exception' => $e->getMessage(), 'str' => $str, 'params' => $params]);
                return '';
            }
            if ($this->cache) {
                $this->cacheMap[$str] = $msgFmt;
            }
        }
        $formatted = $msgFmt->format($params);
        if ($formatted === false) {
            $this->warn('Failed to format using MessageFormatter', ['str' => $str, 'params' => $params]);
            $formatted = '';
        }
        return $formatted;
    }

    protected function warn(string $errorMsg, array $data): void
    {
        $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, 2);
        trigger_error($errorMsg . ', data: ' . $data, E_USER_NOTICE);
    }
}
