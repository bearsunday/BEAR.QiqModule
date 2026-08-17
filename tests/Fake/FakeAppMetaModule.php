<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\AppMeta\AbstractAppMeta;
use Ray\Di\AbstractModule;

final class FakeAppMetaModule extends AbstractModule
{
    /** @param non-empty-string $appDir */
    public function __construct(private string $appDir, AbstractModule|null $module = null)
    {
        parent::__construct($module);
    }

    protected function configure(): void
    {
        $this->bind(AbstractAppMeta::class)->toInstance(new FakeAppMeta($this->appDir));
    }
}
