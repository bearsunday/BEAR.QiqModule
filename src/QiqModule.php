<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class QiqModule extends AbstractModule
{
    public function __construct(
        private string $templateDir,
        private ?AbstractModule $module = null
    ) {
        parent::__construct($this->module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_template_dir')->toInstance($this->templateDir);
        $this->bind(RenderInterface::class)->to(QiqRenderer::class)->in(Scope::SINGLETON);

        $this->bind(RenderInterface::class)->annotatedWith('error_page')->to(QiqErrorPageRenderer::class);
        $this->bind(ErrorInterface::class)->to(QiqErrorHandler::class);
        $this->bind(QiqErrorPage::class);
    }
}
