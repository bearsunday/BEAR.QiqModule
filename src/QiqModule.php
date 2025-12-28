<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use Qiq\Catalog;
use Qiq\Compiler;
use Qiq\Compiler\QiqCompiler;
use Qiq\Engine;
use Qiq\Helper\Html\HtmlHelpers;
use Qiq\Helpers;
use Qiq\Template;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class QiqModule extends AbstractModule
{
    public function __construct(
        private string $templateDir,
        private AbstractModule|null $module = null,
    ) {
        parent::__construct($this->module);
    }

    protected function configure(): void
    {
        $this->bind(Template::class)->in(Scope::SINGLETON);
        $this->bind(Engine::class)->to(Template::class)->in(Scope::SINGLETON);
        $this->bind(Catalog::class)->toConstructor(
            Catalog::class,
            ['paths' => 'qiq_paths', 'extension' => 'qiq_extension'],
        );
        $this->bind()->annotatedWith('qiq_template_dir')->toInstance($this->templateDir);
        $this->bind()->annotatedWith('qiq_paths')->toInstance([$this->templateDir]);
        $this->bind()->annotatedWith('qiq_extension')->toInstance('.php');
        $this->bind(RenderInterface::class)->to(QiqRenderer::class)->in(Scope::SINGLETON);
        $this->bind(Helpers::class)->to(HtmlHelpers::class);
        $this->bind(Compiler::class)->to(QiqCompiler::class);
    }
}
