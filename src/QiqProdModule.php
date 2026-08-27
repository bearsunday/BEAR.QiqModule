<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use Qiq\Catalog;
use Qiq\Compiler;
use Qiq\Compiler\NonCompiler;
use Qiq\Compiler\QiqCompiler;
use Ray\Di\AbstractModule;
use Ray\Di\MultiBinder;
use ReflectionException;

use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Without a path, templates are compiled ahead into {buildDir}/qiq by the compile step and
 * served read-only; serving before compiling raises TemplateNotCompiledException.
 * With a path, templates compile at serve time into it, as before 2.1 — deprecated,
 * and the parameter goes away in 3.0.
 */
final class QiqProdModule extends AbstractModule
{
    public function __construct(private string|null $cachePath = null, AbstractModule|null $module = null)
    {
        if ($this->cachePath !== null) {
            trigger_error('QiqProdModule($cachePath) is deprecated; install QiqProdModule() and compile ahead of serving.', E_USER_DEPRECATED);
        }

        parent::__construct($module);
    }

    /** @throws ReflectionException */
    protected function configure(): void
    {
        if ($this->cachePath !== null) {
            $this->bind()->annotatedWith('qiq_cache_path')->toInstance($this->cachePath);
            $this->bind(Compiler::class)->toConstructor(QiqCompiler::class, ['cachePath' => 'qiq_cache_path']);

            return;
        }

        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding(QiqCompileStep::NAME)->to(QiqCompileStep::class);
        $this->bind(Catalog::class)->to(QiqProdCatalog::class);
        $this->bind(Compiler::class)->to(NonCompiler::class);
    }
}
