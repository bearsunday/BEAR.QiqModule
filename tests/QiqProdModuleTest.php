<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;
use Qiq\Compiler\Compiler;
use Qiq\Compiler\QiqCompiler;
use Ray\Di\Injector;

use function md5;
use function serialize;

class QiqProdModuleTest extends TestCase
{
    private QiqProdModule $qiqProdModule;
    private Compiler $compiler;
    private string $cachePath;

    protected function setUp(): void
    {
        $this->qiqProdModule = new QiqProdModule('/path/to/cache', new QiqModule('templateDir'));
        $injector = new Injector($this->qiqProdModule);
        /** @var string $cachePath */
        $cachePath = $injector->getInstance('', 'qiq_cache_path');
        $this->cachePath = $cachePath;
        /** @var Compiler $compiler */
        $compiler = $injector->getInstance(Compiler::class);
        $this->compiler = $compiler;
    }

    public function testIsInstanceOfQiqModule(): void
    {
        $actual = $this->qiqProdModule;
        $this->assertInstanceOf(QiqProdModule::class, $actual);

        self::assertSame('/path/to/cache', $this->cachePath);
        self::assertSame(md5(serialize(new QiqCompiler('/path/to/cache'))), md5(serialize($this->compiler)));
    }
}
