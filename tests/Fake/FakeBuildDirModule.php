<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\Annotation\BuildDir;
use Ray\Di\AbstractModule;

final class FakeBuildDirModule extends AbstractModule
{
    public function __construct(private string $buildDir, AbstractModule|null $module = null)
    {
        parent::__construct($module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith(BuildDir::class)->toInstance($this->buildDir);
    }
}
