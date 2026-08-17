<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use Qiq\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\MultiBinder;
use ReflectionException;

final class QiqProdModule extends AbstractModule
{
    /** @param string|null $cachePath stands in for #[BuildDir]: templates are read from {$cachePath}/qiq */
    public function __construct(
        private string|null $cachePath = null,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    /** @throws ReflectionException */
    protected function configure(): void
    {
        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding(QiqCompileStep::NAME)->to(QiqCompileStep::class);
        if ($this->cachePath === null) {
            $this->bind(Compiler::class)->to(QiqServeCompiler::class);

            return;
        }

        $this->bind()->annotatedWith('qiq_cache_path')->toInstance($this->cachePath);
        $this->bind(Compiler::class)->toConstructor(
            QiqServeCompiler::class,
            ['buildDir' => 'qiq_cache_path', 'paths' => 'qiq_paths'],
        );
    }
}
