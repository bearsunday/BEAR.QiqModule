<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\QiqModule\Exception\TemplateNotCompiledException;
use Qiq\Catalog;
use Qiq\Compiler;
use Ray\Di\Di\Named;

use function is_file;

/**
 * Resolves a template by name under the build directory, never through the template tree
 *
 * Qiq\Catalog stats the source before it compiles, so prod would otherwise need the templates
 * as well as their artifacts. A name carries no absolute path, so it resolves under a
 * `phar://` appDir like any other.
 */
final class QiqProdCatalog extends Catalog
{
    private readonly string $compiledDir;

    public function __construct(
        AbstractAppMeta $meta,
        Compiler $compiler,
        #[Named('qiq_extension')] string $extension = '.php',
    ) {
        parent::__construct([], $extension, $compiler);
        // Qiq\Catalog assigns it through a public setter, which static analysis does not trace
        $this->extension = $extension;
        $this->compiledDir = $meta->buildDir . '/' . QiqCompileStep::NAME;
    }

    public function has(string $name): bool
    {
        return is_file($this->file($name));
    }

    /** @throws TemplateNotCompiledException */
    public function getCompiled(string $name): string
    {
        $file = $this->file($name);
        if (! is_file($file)) {
            throw new TemplateNotCompiledException($name);
        }

        return $file;
    }

    private function file(string $name): string
    {
        return $this->compiledDir . '/' . TemplateKey::ofName($name, $this->extension);
    }
}
