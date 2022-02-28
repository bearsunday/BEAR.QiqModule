<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function dirname;

class QiqRendererTest extends TestCase
{
    private AbstractModule $module;

    protected function setUp(): void
    {
        $qiqTemplateDir = dirname(__DIR__) . '/tests/Fake/templates';
        $this->module = new QiqModule($qiqTemplateDir);
        parent::setUp();
    }

    public function testRenderModule(): void
    {
        $render = (new Injector($this->module))->getInstance(RenderInterface::class);
        $this->assertInstanceOf(QiqRenderer::class, $render);
    }

    public function testRender(): void
    {
        $ro = (new Injector($this->module))->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);
        $ro = $ro->onGet(['name' => 'World']);
        $view = (string) $ro;
        $this->assertSame('Hello, World. That was Qiq! And this is PHP, World.
', $view);
    }

    public function testRenderWeavedResource(): void
    {
        $ro = (new Injector($this->module))->getInstance(FakeWeavedRo::class);
        assert($ro instanceof FakeWeavedRo);
        $ro = $ro->onGet(['name' => 'World']);
        $view = (string) $ro;
        $this->assertSame('Hello, World. That was Qiq! And this is PHP, World.
', $view);
    }

    public function testCacheRender(): void
    {
        $cachePath = __DIR__ . '/tmp';
        $this->module->install(new QiqProdModule($cachePath));
        $ro = (new Injector($this->module))->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);
        $ro = $ro->onGet(['name' => 'World']);
        $view = (string) $ro;
        $this->assertSame('Hello, World. That was Qiq! And this is PHP, World.
', $view);
    }
}
