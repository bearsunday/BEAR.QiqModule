<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class QiqModule extends AbstractModule
{
    /** @var string */
    private $templateDir;

    public function __construct(string $templateDir, ?AbstractModule $module = null)
    {
        $this->templateDir = $templateDir;
        parent::__construct($module);
    }

    protected function configure(): void
    {
        $this->bind()->annotatedWith('qiq_template_dir')->toInstance($this->templateDir);
        $this->bind(RenderInterface::class)->to(QiqRenderer::class)->in(Scope::SINGLETON);
    }
}
