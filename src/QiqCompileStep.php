<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use Qiq\Catalog;
use Ray\Di\Di\Named;

use function array_unique;
use function count;
use function is_dir;

final class QiqCompileStep implements CompileStepInterface
{
    /** Binding key of this step, and the sub directory of the build directory it owns */
    public const NAME = 'qiq';

    /**
     * Resolved at compile, in every tree that installs QiqProdModule. A tree without QiqModule
     * binds no template path, and the defaults let the step compile nothing instead of failing
     * the compile with Unbound. prod-cli-hal-app beside prod-html-app is that tree.
     *
     * @param list<string> $paths
     */
    public function __construct(
        #[Named('qiq_paths')]
        private readonly array $paths = [],
        #[Named('qiq_extension')]
        private readonly string $extension = '.php',
    ) {
    }

    public function __invoke(string $stepDir): int
    {
        if ($this->paths === []) {
            return 0; // no QiqModule in this tree
        }

        $key = new TemplateKey($this->paths);
        $compiler = new QiqBuildCompiler($stepDir, $key);
        // clean build: "first root wins" needs the leftovers of earlier runs gone
        $compiler->clear();
        $catalog = new Catalog($this->specs($key), $this->extension, $compiler);

        // first-root-wins duplicates return the same artifact path, so count distinct artifacts
        return count(array_unique($catalog->compileAll()));
    }

    /**
     * @return list<string>
     *
     * @see Catalog::compileAll() its RecursiveDirectoryIterator throws on a missing root
     */
    private function specs(TemplateKey $key): array
    {
        $specs = [];
        foreach ($key->roots() as $collection => $roots) {
            foreach ($roots as $root) {
                if (! is_dir($root)) {
                    continue;
                }

                $specs[] = $collection === TemplateKey::DEFAULT_COLLECTION ? $root : $collection . ':' . $root;
            }
        }

        return $specs;
    }
}
