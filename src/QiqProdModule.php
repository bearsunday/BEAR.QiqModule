<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Ray\Di\AbstractModule;

final class QiqProdModule extends AbstractModule
{
    public function __construct(private string $cachePath, private ?AbstractModule $module = null)
    {
        parent::__construct($this->module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_cache_path')->toInstance($this->cachePath);
    }
}
