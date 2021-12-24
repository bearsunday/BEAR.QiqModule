<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;

class QiqRendererTest extends TestCase
{
    /** @var RenderInterface */
    protected $renderer;

    protected function setUp(): void
    {
        $renderer = (new Injector(new QiqModule()))->getInstance(QiqRenderer::class);
        assert($renderer instanceof RenderInterface);
        $this->renderer = $renderer;
    }

    public function testRender(): void
    {
        $ro = (new FakeRo())->onGet(['name' => 'BEAR']);
        $view = (string) $ro;
        $this->assertSame('Hello BEAR', $view);
    }
}
