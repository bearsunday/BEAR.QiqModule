<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateNotCompiledException;
use BEAR\Sunday\Compile\Annotation\BuildDir;
use Qiq\Compiler;
use Ray\Di\Di\Named;

use function is_file;
use function rtrim;

/** Reads what QiqCompileStep wrote: serving never touches the filesystem for writes */
final class QiqServeCompiler implements Compiler
{
    private string $compiledDir;
    private TemplateKey $key;

    /** @param list<string> $paths */
    public function __construct(
        #[BuildDir] string $buildDir,
        #[Named('qiq_paths')] array $paths,
    ) {
        $this->compiledDir = rtrim($buildDir, '/') . '/' . QiqCompileStep::NAME;
        $this->key = new TemplateKey($paths);
    }

    /** @throws TemplateNotCompiledException */
    public function compile(string $source): string
    {
        $cached = $this->compiledDir . '/' . ($this->key)($source);
        // not a "has it changed?" stat: it turns a read-only filesystem into a named error
        if (! is_file($cached)) {
            throw new TemplateNotCompiledException($source);
        }

        return $cached;
    }

    public function clear(): void
    {
        // build artifacts outlive the request
    }
}
