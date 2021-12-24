<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class QiqRendererTest extends TestCase
{

    public function testRender(): void
    {
        $ro = (new Injector(new QiqModule()))->getInstance(FakeRo::class);
        $ro = $ro->onGet(['name' => 'BEAR']);
        $view = (string) $ro;
        $this->assertSame('Hello, World. That was Qiq! And this is PHP, World.
', $view);
    }
}
