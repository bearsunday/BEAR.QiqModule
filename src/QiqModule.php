<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class QiqModule extends AbstractModule
{
    public function __construct(
        private string $templateDir,
        private ?string $errorViewName = null,
        private ?AbstractModule $module = null
    ) {
        parent::__construct($this->module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_template_dir')->toInstance($this->templateDir);
        $this->bind(RenderInterface::class)->to(QiqRenderer::class)->in(Scope::SINGLETON);
        $this->install(new QiqErrorModule($this->errorViewName));
    }
}
