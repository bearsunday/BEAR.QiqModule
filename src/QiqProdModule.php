<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use Qiq\Catalog;
use Qiq\Compiler;
use Qiq\Compiler\NonCompiler;
use Ray\Di\AbstractModule;
use Ray\Di\MultiBinder;
use ReflectionException;

final class QiqProdModule extends AbstractModule
{
    /** @throws ReflectionException */
    protected function configure(): void
    {
        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding(QiqCompileStep::NAME)->to(QiqCompileStep::class);
        $this->bind(Catalog::class)->to(QiqProdCatalog::class);
        $this->bind(Compiler::class)->to(NonCompiler::class);
    }
}
