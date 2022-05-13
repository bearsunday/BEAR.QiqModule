<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Qiq\Compiler\Compiler;
use Qiq\Compiler\QiqCompiler;
use Ray\Di\AbstractModule;
use ReflectionException;

final class QiqProdModule extends AbstractModule
{
    public function __construct(private string $cachePath, private ?AbstractModule $module = null)
    {
        parent::__construct($this->module);
    }

    /**
     * @throws ReflectionException
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_cache_path')->toInstance($this->cachePath);
        $this->bind(Compiler::class)->toConstructor(QiqCompiler::class, ['cachePath' => 'qiq_cache_path']);
    }
}
