<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateNotWrittenException;
use Qiq\Compiler\QiqCompiler;

use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function rename;
use function rtrim;

final class QiqBuildCompiler extends QiqCompiler
{
    private string $compiledDir;

    public function __construct(string $compiledDir, private TemplateKey $key)
    {
        $this->compiledDir = rtrim($compiledDir, '/');
        parent::__construct($this->compiledDir);
    }

    /** @throws TemplateNotWrittenException */
    public function compile(string $source): string
    {
        $cached = $this->compiledDir . '/' . ($this->key)($source);
        // first root wins, as Catalog::source() resolves by the first readable path
        if (is_file($cached)) {
            return $cached;
        }

        $dir = dirname($cached);
        if (! is_dir($dir) && ! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new TemplateNotWrittenException($dir);
        }

        $tmp = $cached . '.tmp';
        $code = $this->convert((string) file_get_contents($source));
        // tmp + rename: a half written artifact is indistinguishable from a compiled one
        if (@file_put_contents($tmp, $code) === false || ! @rename($tmp, $cached)) {
            throw new TemplateNotWrittenException($cached);
        }

        return $cached;
    }
}
