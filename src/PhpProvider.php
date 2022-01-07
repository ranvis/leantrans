<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

class PhpProvider implements ProviderInterface
{
    /**
     * Instantiate a PHP script data provider.
     *
     * @param array $domains
     * List of valid domains ['domain' => 'file_path', ...]
     * If file_path is null, file at $dir/domain.php is loaded.
     * @param string|null $dir The default directory when a domain's file_path is null
     * @param string $defaultDomain The file name (without .php extension) for the default domain ''
     */
    public function __construct(
        private array $domains,
        private ?string $dir = null,
        private string $defaultDomain = 'messages',
    ) {
    }

    public static function compile(array $msgs): string
    {
        return '<?php return ' . var_export($msgs, true) . ';';
    }

    public function addDomain(string $domain, ?string $path): void
    {
        $this->domains[$domain] = $path;
    }

    protected function loadDomain(string $domain): array
    {
        $path = $this->domains[$domain] ?? null;
        if ($path === null) {
            $fileName = (($domain === '') ? $this->defaultDomain : $domain) . '.php';
            if ($this->dir === null) {
                throw new \UnexpectedValueException('The default directory for domain is not specified.');
            }
            $path = $this->dir . DIRECTORY_SEPARATOR . $fileName;
        }
        $msgs = require($path);
        if (!\is_array($msgs)) {
            throw new \UnexpectedValueException('PHP script should return an array of translations: ' . $path);
        }
        return $msgs;
    }

    public function query(string $msg, string $domain): string
    {
        if (!array_key_exists($domain, $this->domains)) {
            return $msg;
        }
        $msgs = $this->domains[$domain];
        if (!\is_array($msgs)) {
            $this->domains[$domain] = $msgs = $this->loadDomain($domain);
        }
        return $msgs[$msg] ?? $msg;
    }
}
