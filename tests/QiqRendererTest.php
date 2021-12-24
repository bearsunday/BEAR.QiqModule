<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function dirname;

class QiqRendererTest extends TestCase
{
    public function testRender(): void
    {
        $qiqTemplateDir = dirname(__DIR__) . '/tests/Fake/templates';
        $ro = (new Injector(new QiqModule($qiqTemplateDir)))->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);
        $ro = $ro->onGet(['name' => 'World']);
        $view = (string) $ro;
        $this->assertSame('Hello, World. That was Qiq! And this is PHP, World.
', $view);
    }
}
