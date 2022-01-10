<?php
/*
 * @author SATO Kentaro
 * @license BSD-2-Clause
 */

declare(strict_types=1);

namespace Ranvis\LeanTrans;

use PHPUnit\Framework\TestCase;
use Ranvis\LeanTrans\TestUtil\TempFile;

/**
 * @covers \Ranvis\LeanTrans\PhpProvider
 */
class PhpProviderTest extends TestCase
{
    public function testScript(): void
    {
        $script = PhpProvider::compile(['in' => 'out']);
        $file = new TempFile($script);
        $instance = new PhpProvider(['' => $file->getPath()]);
        $this->assertSame('out', $instance->query('in', ''));
        $this->assertSame('msg', $instance->query('msg', ''));
        $this->assertSame('in', $instance->query('in', 'domain'));
        $instance->addDomain('domain', $file->getPath());
        $this->assertSame('out', $instance->query('in', 'domain'));
    }

    public function testDirScript(): void
    {
        $script = PhpProvider::compile(['in' => 'out2']);
        $subFile = new TempFile($script);
        $script = PhpProvider::compile(['in' => 'out']);
        $file = new TempFile($script, $subFile->getPath() . '-main');
        $mainName = basename($file->getPath());
        $subDir = dirname($subFile->getPath());
        $domain = basename($subFile->getPath());
        $domain = preg_replace_callback('/[^\w]/', fn ($m) => '-' . bin2hex($m[0]), $domain);
        $file->rename($file->getPath() . '.php');
        $subPath = $subDir . '/' . $domain . '.php';
        $subFile->rename($subPath);
        $instance = new PhpProvider(['' => null, $domain => null], $subDir, $mainName);
        $this->assertSame('out', $instance->query('in', ''));
        $this->assertSame('out2', $instance->query('in', $domain));
    }

    public function testNoDirSpecified(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $instance = new PhpProvider(['' => null]);
        $instance->query('msg', '');
    }

    public function testLoadBadCode(): void
    {
        $script = '<?php bad code';
        $php = new TempFile($script);
        $instance = new PhpProvider(['' => $php->getPath()]);
        $this->expectException(\Error::class);
        $instance->query('in', '');
    }

    public function testLoadNonArray(): void
    {
        $script = '<?php return 123;';
        $php = new TempFile($script);
        $instance = new PhpProvider(['' => $php->getPath()]);
        $this->expectException(\UnexpectedValueException::class);
        $instance->query('in', '');
    }
}
