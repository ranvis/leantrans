<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans\TestUtil;

class TempFile
{
    private string $path;

    public function __construct(string $script, string $path = null)
    {
        if ($path === null) {
            $dir = getenv('TEMP') ?? __DIR__;
            if (($path = tempnam($dir, 'tmpScript')) === false) {
                throw new \RuntimeException('Failed to set up a temporary file');
            }
        } else {
            $this->createFile($path);
        }
        $this->path = $path;
        if (file_put_contents($path, $script) === false) {
            throw new \RuntimeException('Failed to write to a temporary file');
        }
    }

    private function createFile(string $path): void
    {
        if (!fopen($path, 'xb')) {
            throw new \RuntimeException('Temporary file path collision.');
        }
    }

    public function __destruct()
    {
        unlink($this->path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function rename(string $newPath): void
    {
        $this->createFile($newPath);
        if (!rename($this->path, $newPath)) {
            throw new \RuntimeException('Cannot rename temporary file.');
        }
        // The file will still be removed on destruction.
        $this->path = $newPath;
    }
}
