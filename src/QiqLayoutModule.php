<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Ray\Di\AbstractModule;

final class QiqLayoutModule extends AbstractModule
{
    public const DEFAULT_LAYOUT = 'default';

    public function __construct(
        private string $layout = self::DEFAULT_LAYOUT,
        private ?AbstractModule $module = null
    ) {
        parent::__construct($this->module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_layout')->toInstance('/layout/' . $this->layout);
    }
}
